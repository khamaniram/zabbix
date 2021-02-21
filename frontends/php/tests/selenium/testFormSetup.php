<?php
/*
** Zabbix
** Copyright (C) 2001-2020 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

require_once dirname(__FILE__) . '/../include/CWebTest.php';
require_once dirname(__FILE__).'/traits/TableTrait.php';
require_once dirname(__FILE__).'/behaviors/CMessageBehavior.php';

/**
 * @backup sessions
 */
class testFormSetup extends CWebTest {

	use TableTrait;

	/**
	 * Attach MessageBehavior to the test.
	 *
	 * @return array
	 */
	public function getBehaviors() {
		return [
			'class' => CMessageBehavior::class
		];
	}

	public function testFormSetup_welcomeSectionLayout() {
		$this->page->login()->open('setup.php')->waitUntilReady();

		// Check Welcome section.
		$this->assertEquals("Welcome to\nZabbix 4.0", $this->query('xpath://div[@class="setup-title"]')->one()->getText());
		$this->checkSections('Welcome');
		$this->checkButtons('first section');

		$this->assertScreenshot($this->query('xpath://form')->one(), 'Welcome');
	}

	public function testFormSetup_prerequisitesSectionLayout() {
		$this->page->login()->open('setup.php')->waitUntilReady();
		$this->query('button:Next step')->one()->click();

		// Check Pre-requisites section.
		$this->checkPageTextElements('Check of pre-requisites');
		$headers = $this->query('class:list-table')->asTable()->one()->getHeadersText();
		$this->assertEquals(['', 'Current value', 'Required', ''], $headers);

		$prerequisites = [
			'PHP version',
			'PHP option "memory_limit"',
			'PHP option "post_max_size"',
			'PHP option "upload_max_filesize"',
			'PHP option "max_execution_time"',
			'PHP option "max_input_time"',
			'PHP option "date.timezone"',
			'PHP databases support',
			'PHP bcmath',
			'PHP mbstring',
			'PHP option "mbstring.func_overload"',
			'PHP sockets',
			'PHP gd',
			'PHP gd PNG support',
			'PHP gd JPEG support',
			'PHP gd FreeType support',
			'PHP libxml',
			'PHP xmlwriter',
			'PHP xmlreader',
			'PHP LDAP',
			'PHP ctype',
			'PHP session',
			'PHP option "session.auto_start"',
			'PHP gettext',
			'PHP option "arg_separator.output"'
		];
		$this->assertTableDataColumn($prerequisites, '');
		$this->checkSections('Check of pre-requesties');
		$this->checkButtons();

		global $DB;
		$this->assertScreenshot($this->query('xpath://form')->one(), 'Prerequisites_'.$DB['TYPE']);
	}

	public function testFormSetup_dbConnectionSectionLayout() {
		$this->openSpecifiedSection('Configure DB connection');

		// Check Configure DB connection section.
		$fields = [
			'Database host' => 'localhost',
			'Database port' => '0',
			'Database name' => 'zabbix',
			'User' => 'zabbix',
			'Password' => ''
		];
		$text = 'Please create database manually, and set the configuration parameters for connection to this database. '.
				'Press "Next step" button when done.';
		$this->checkPageTextElements('Configure DB connection', $text);
		$form = $this->query('xpath://form')->asForm()->one();

		// Check input fields in Configure DB connection section for each DB type.
		$db_types = $form->getField('Database type')->getOptions()->asText();
		foreach ($db_types as $db_type) {
			$form->getField('Database type')->select($db_type);
			$form->invalidate();
			if ($db_type === 'PostgreSQL') {
				$schema_field = $form->getField('Database schema');
				$this->assertEquals(255, $schema_field->getAttribute('maxlength'));
			}

			foreach ($fields as $field_name => $field_value) {
				$maxlength = ($field_name === 'Database port') ? 5 : 255;
				$field = $form->getField($field_name);
				$this->assertEquals($field_value, $field->getValue());
				$this->assertEquals($maxlength, $field->getAttribute('maxlength'));
			}

			// Array of fields to be skipped by the screenshot check.
			$skip_db_fields = [];
			foreach(['Database host', 'Database name'] as $skip_field) {
				$skip_db_fields[] = $form->getField($skip_field);
			}
			$this->assertScreenshotExcept($form, $skip_db_fields, 'ConfigureDB_'.$db_type);
		}
	}

	public function testFormSetup_zabbixServerSectionLayout() {
		$this->openSpecifiedSection('Zabbix server details');

		// Check Zabbix server details section.
		$server_params = [
			'Host' => 'localhost',
			'Port' => '10051',
			'Name' => ''
		];
		$text = 'Please enter the host name or host IP address and port number of the Zabbix server, as well as the '.
				'name of the installation (optional).';
		$this->checkPageTextElements('Zabbix server details', $text);

		$form = $this->query('xpath://form')->asForm()->one();
		foreach ($server_params as $field_name => $value) {
			$maxlength = ($field_name === 'Port') ? 5 : 255;
			$field = $form->getField($field_name);
			$this->assertEquals($maxlength, $field->getAttribute('maxlength'));
			$this->assertEquals($value, $field->getValue());
		}

		$this->checkButtons();
		$this->assertScreenshot($form, 'ZabbixServerDetails');
	}

	public function testFormSetup_summarySection() {
		$this->openSpecifiedSection('Pre-installation summary');
		$db_parameters = $this->getDbParameters();
		$text = 'Please check configuration parameters. If all is correct, press "Next step" button, or "Back" button '.
				'to change configuration parameters.';
		$this->checkPageTextElements('Pre-installation summary', $text);

		$summary_fields = [
			'Database server' => $db_parameters['Database host'],
			'Database name' => $db_parameters['Database name'],
			'Database user' => $db_parameters['User'],
			'Database password' => '******',
			'Zabbix server' => 'localhost',
			'Zabbix server port' => '10051',
			'Zabbix server name' => ''
		];

		if ($db_parameters['Database type'] === 'PostgreSQL') {
			$summary_fields['Database type'] = 'PostgreSQL';
			$summary_fields['Database schema'] = '';
		}
		else {
			$summary_fields['Database type'] = 'MySQL';
			$this->assertFalse($this->query('xpath://span[text()="Database schema"]')->one(false)->isValid());
		}
		$summary_fields['Database port'] = ($db_parameters['Database port'] === '0') ? 'default' : $db_parameters['Database port'];
		foreach ($summary_fields as $field_name => $value) {
			$xpath = 'xpath://span[text()='.CXPathHelper::escapeQuotes($field_name).']/../../div[@class="table-forms-td-right"]';
			// Assert contains is used as Password length can differ.
			if ($field_name === 'Database password') {
				$this->assertContains($value, $this->query($xpath)->one()->getText());
			}
			else {
				$this->assertEquals($value, $this->query($xpath)->one()->getText());
			}
		}
		$this->checkButtons();

		// Check screenshot of the Pre-installation summary section.
		$skip_fields = [];
		foreach(['Database server', 'Database name'] as $skip_field) {
			$xpath = 'xpath://span[text()='.CXPathHelper::escapeQuotes($skip_field).']/../../div[@class="table-forms-td-right"]';
			$skip_fields[] = $this->query($xpath)->one();
		}
		$this->assertScreenshotExcept($this->query('xpath://form')->one(), $skip_fields, 'PreInstall_'.$db_parameters['Database type']);
	}

	public function testFormSetup_installSection() {
		$this->openSpecifiedSection('Install');
		$this->checkPageTextElements('Install', '/conf/zabbix.conf.php" created.');
		$this->assertEquals('Congratulations! You have successfully installed Zabbix frontend.',
				$this->query('class:green')->one()->getText());
		$this->checkButtons('last section');
		$this->assertScreenshotExcept($this->query('xpath://form')->one(), $this->query('xpath://p')->one(), 'Install');

		// Check that Dashboard view is opened after completing the form.
		$this->query('button:Finish')->one()->click();
		$this->page->waitUntilReady();
		$this->assertContains('zabbix.php?action=dashboard.view', $this->page->getCurrentURL());
	}

	public function getDbConnectionDetails() {
		return [
			// Incorrect DB host.
			[
				[
					'expected' => TEST_BAD,
					'field' => [
						'name' => 'Database host',
						'value'=> 'incorrect_DB_host'
					]
				]
			],
			// Partially non-numeric port number.
			[
				[
					'expected' => TEST_BAD,
					'field' => [
						'name' => 'Database port',
						'value' => '123aaa'
					],
					'check_port' => 123
				]
			],
			// Large port number.
			[
				[
					'expected' => TEST_BAD,
					'field' => [
						'name' => 'Database port',
						'value' => '99999'
					],
					'error_details' => 'Incorrect value "99999" for "Database port" field: must be between 0 and 65535.'
				]
			],
			// Incorrect DB name.
			[
				[
					'expected' => TEST_BAD,
					'field' => [
						'name' => 'Database name',
						'value' => 'Wrong database name'
					]
				]
			],
			// Incorrect DB schema for PostgreSQL.
			[
				[
					'expected' => TEST_BAD,
					'field' => [
						'name' => 'Database schema',
						'value' => 'incorrect schema'
					],
					'error_details' => 'Unable to determine current Zabbix database version: the table "dbversion" was not found.'
				]
			],
			// Incorrect user name.
			[
				[
					'expected' => TEST_BAD,
					'field' => [
						'name' => 'User',
						'value' => 'incorrect user name'
					]
				]
			],
			// Set incorrect password.
			[
				[
					'expected' => TEST_BAD,
					'field' => [
						'name' => 'Password',
						'value' => 'this_password_is_incorrect'
					]
				]
			],
			// Non-numeric port.
			[
				[
					'field' => [
						'name' => 'Database port',
						'value' => 'aaa1'
					],
					'check_port' => 0
				]
			],
			// Non-default port.
			[
				[
					'field' => [
						'name' => 'Database port',
						'value' => 'should_be_changed'
					],
					'change_port' => true
				]
			]
		];
	}

	/**
	 * @dataProvider getDbConnectionDetails
	 */
	public function testFormSetup_dbConfigSectionParameters($data) {
		// Prepare array with DB parameter values.
		$db_parameters = $this->getDbParameters();
		$db_parameters[$data['field']['name']] = $data['field']['value'];

		// Use default database port if specified in data provider.
		if (array_key_exists('change_port', $data)) {
			$db_parameters['Database port'] = ($db_parameters['Database type'] === 'PostgreSQL') ? 5432 : 3306;
		}

		// Skip the case with invalid DB schema if DB type is MySQL.
		if ($data['field']['name'] === 'Database schema' && $db_parameters['Database type'] === 'MySQL') {

			return;
		}

		// Open "Configure DB connection" section.
		$this->openSpecifiedSection('Configure DB connection');

		// Fill Database connection parameters.
		$form = $this->query('xpath://form')->asForm()->one();

		// Workaroung implemented due to ZBX-18688 - Remove the below condition when issue is fixed.
		$db_types = $form->getField('Database type')->getOptions()->asText();
		if ($data['field']['name'] === 'Database schema' && count($db_types) === 1) {

			return;
		}

		$form->fill($db_parameters);

		// Check that port number was trimmed after removing focus, starting with 1st non-numeric symbol.
		if ($data['field']['name'] === 'Database port') {
			$this->page->removeFocus();
		}
		$form->invalidate();
		if (array_key_exists('check_port', $data)) {
			$this->assertEquals($data['check_port'], $form->getField('Database port')->getValue());
		}

		// Check the outcome for the specified database configuration.
		$this->query('button:Next step')->one()->click();
		if (CTestArrayHelper::get($data, 'expected', TEST_GOOD) === TEST_BAD) {
			$error_details = CTestArrayHelper::get($data, 'error_details', 'Error connecting to database');
			$this->assertMessage(TEST_BAD, 'Cannot connect to the database.', $error_details);
		}
		else {
			$this->assertEquals('Zabbix server details', $this->query('xpath://h1')->one()->getText());
		}
	}

	public function testFormSetup_zabbixServerSectionParameters() {
		// Open Zabbix server configuration section.
		$this->openSpecifiedSection('Zabbix server details');

		$server_parameters = [
			'Host' => 'Zabbix_server_imaginary_host',
			'Port' => '65535',
			'Name' => 'Zabbix_server_imaginary_name'
		];
		$form = $this->query('xpath://form')->asForm()->one();

		// Check that port number was trimmed after removing focus and now is set to 0.
		$form->getField('Port')->fill('a999');
		$this->page->removeFocus();
		$this->assertEquals(0, $form->getField('Port')->getValue());

		// Check that port number higher than 65535 is not accepted.
		// Uncomment the below section once ZBX-18627 is merged.
//		$form->getField('Port')->fill('65536');
//		$this->query('button:Next step')->one()->click();
//		$this->assertMessage(TEST_BAD, 'Cannot connect to the database.', 'Incorrect value "99999" for "Database port" '.
//				'field: must be between 0 and 65535.');

		$form->fill($server_parameters);
		$this->query('button:Next step')->one()->click();

		// Check that the fields are filled correctly in the Pre-installation summary section.
		$summary_fields = [
			'Zabbix server' => $server_parameters['Host'],
			'Zabbix server port' => $server_parameters['Port'],
			'Zabbix server name' => $server_parameters['Name']
		];

		foreach ($summary_fields as $field_name => $value) {
			$xpath = 'xpath://span[text()='.CXPathHelper::escapeQuotes($field_name).']/../../div[@class="table-forms-td-right"]';
			$this->assertEquals($value, $this->query($xpath)->one()->getText());
		}
		$this->query('button:Next step')->one()->click();

		// Need to wait for 3s for php cache to reload and for zabbix server parameter the changes to take place.
		sleep(3);
		$this->query('button:Finish')->one()->click();

		// Check Zabbix server params.
		$this->assertEquals($server_parameters['Name'].': Dashboard', $this->page->getTitle());
		$system_info = CDashboardElement::find()->one()->getWidget('System information')->getContent();
		$this->assertContains($server_parameters['Host'].':'.$server_parameters['Port'], $system_info->getText());
	}

	public function testFormSetup_backButtons() {
		// Open the Pre-installation summary section.
		$this->openSpecifiedSection('Pre-installation summary');

		// Proceed back to the 1st section of the setup form.
		$this->query('button:Back')->one()->click();
		$this->assertEquals('Zabbix server details', $this->query('xpath://h1')->one()->getText());
		$this->query('button:Back')->one()->click();
		$this->assertEquals('Configure DB connection', $this->query('xpath://h1')->one()->getText());
		$this->query('button:Back')->one()->click();
		$this->assertEquals('Check of pre-requisites', $this->query('xpath://h1')->one()->getText());
		$this->query('button:Back')->one()->click();
		$this->assertEquals("Welcome to\nZabbix 4.0", $this->query('xpath://div[@class="setup-title"]')->one()->getText());
		$this->checkSections('Welcome');
		$this->checkButtons('first section');

		// Cancel setup form update.
		$this->query('button:Cancel')->one()->click();
		$this->assertContains('zabbix.php?action=dashboard.view', $this->page->getCurrentURL());
	}

	public function testFormSetup_restoreServerConfig() {
		// Open the last section of the setup form.
		$this->openSpecifiedSection('Zabbix server details');
		// Restore Zabbix server name field value.
		$form = $this->query('xpath://form')->asForm()->one();
		$form->getField('Name')->fill('TEST_SERVER_NAME');
		$this->query('button:Next step')->one()->click();
		$this->query('button:Next step')->one()->click();

		// Need to wait for 3s for php cache to reload and for zabbix server parameter the changes to take place.
		sleep(3);
		$this->query('button:Finish')->one()->click();
	}

	/**
	 * Function checks the title of the current section, section navigation column and presence of text if defined.
	 *
	 * @param	string	$title		title of the current setup form section
	 * @param	string	$text		text that should be present in a paragraph of the current setup form section
	 */
	private function checkPageTextElements($title, $text = null) {
		$this->assertTrue($this->query('xpath://h1[text()='.CXPathHelper::escapeQuotes($title).']')->one()->isValid());
		$this->checkSections($title);
		if ($text) {
			$this->assertContains($text, $this->query('xpath:.//p')->one()->getText());
		}
	}

	/**
	 * Function checks if the buttons on the currently opened setup form section are clickable.
	 *
	 * @param	string	$section	position of current section in the form (first, last, middle)
	 */
	private function checkButtons($section = 'middle section') {
		switch ($section) {
			case 'first section':
				$buttons = [
					'Cancel' => true,
					'Back' => false,
					'Next step' => true
				];
				break;

			case 'last section':
				$buttons = [
					'Cancel' => false,
					'Back' => false,
					'Finish' => true
				];
				break;

			case 'middle section':
				$buttons = [
					'Cancel' => true,
					'Back' => true,
					'Next step' => true
				];
				break;
		}

		foreach ($buttons as $button => $clickable) {
			$this->assertEquals($clickable, $this->query('button', $button)->one()->isCLickable());
		}
	}

	/**
	 * Function checks that all sections are present in the section navigation column, and that the current (or all
	 * section) are grayed out.
	 *
	 * @param	string	$current	title of the current setup form section.
	 */
	private function checkSections($current) {
		$sections = [
			'Welcome',
			'Check of pre-requisites',
			'Configure DB connection',
			'Zabbix server details',
			'Pre-installation summary',
			'Install'
		];

		foreach ($sections as $section_name) {
			$section = $this->query('xpath://li[text()='.CXPathHelper::escapeQuotes($section_name).']')->one();
			$this->assertTrue($section->isValid());
			// It is required to check that all sections are grayed out because Install is the last step.
			if ($section_name === $current || $current === 'Install') {
				$this->assertEquals('setup-left-current', $section->getAttribute('class'));
			}
		}
	}

	/**
	 * Function opens the setup form and navigates to the specified section.
	 *
	 * @param	string	$section	the name of the section to be opened
	 */
	private function openSpecifiedSection($section) {
		$this->page->login()->open('setup.php')->waitUntilReady();
		$this->query('button:Next step')->one()->click();
		$this->query('button:Next step')->one()->click();
		// No actions required in case of Configure DB connection section.
		if ($section === 'Configure DB connection') {
			return;
		}
		// Define the number of clicks on the Next step button depending on the name of the desired section.
		$skip_sections = [
			'Zabbix server details' => 1,
			'Pre-installation summary' => 2,
			'Install' => 3
		];
		// Fill in DB parameters and navigate to the desired section.
		$db_parameters = $this->getDbParameters();
		$form = $this->query('xpath://form')->asForm()->one();
		$form->fill($db_parameters);
		for ($i = 0; $i < $skip_sections[$section]; $i++) {
			$this->query('button:Next step')->one()->click();
		}
	}

	/**
	 * Function retrieves the values to be filled in the Configure DB connection section.
	 *
	 * @return	array
	 */
	private function getDbParameters() {
		global $DB;
		$db_parameters = [
			'Database host' => $DB['SERVER'],
			'Database name' => $DB['DATABASE'],
			'Database port' => $DB['PORT'],
			'User' => $DB['USER'],
			'Password' => $DB['PASSWORD']
		];
		$db_parameters['Database type'] = ($DB['TYPE'] === ZBX_DB_POSTGRESQL) ? 'PostgreSQL' : 'MySQL';

		return $db_parameters;
	}
}
