<?php
/*
** Zabbix
** Copyright (C) 2001-2021 Zabbix SIA
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

require_once dirname(__FILE__).'/../include/CWebTest.php';
require_once dirname(__FILE__).'/behaviors/CMessageBehavior.php';
require_once dirname(__FILE__).'/traits/TableTrait.php';

/**
 * @backup items
 */
class testPageLowLevelDiscovery extends CWebTest {

	use TableTrait;

	const HOST_ID = 90001;

	/**
	 * Attach MessageBehavior to the test.
	 *
	 * @return array
	 */
	public function getBehaviors() {
		return [CMessageBehavior::class];
	}

	public function testPageLowLevelDiscovery_CheckPageLayout() {
		$this->page->login()->open('host_discovery.php?&hostid='.self::HOST_ID);

		// Checking Title, Header and Column names.
		$this->assertPageTitle('Configuration of discovery rules');
		$this->assertPageHeader('Discovery rules');
		$table = $this->query('class:list-table')->asTable()->one();
		$headers = ['', 'Name', 'Items', 'Triggers', 'Graphs', 'Hosts', 'Key', 'Interval', 'Type', 'Status', 'Info'];
		$this->assertSame($headers, $table->getHeadersText());

		// Check that 3 rows displayed
		$this->assertTableStats(3);

		// Check buttons.
		$buttons_name = ['Disable', 'Enable', 'Check now', 'Delete'];
		foreach ($buttons_name as $button) {
			$this->assertTrue($this->query('button:'.$button)->one()->isPresent());
		}
	}

	public function testPageLowLevelDiscovery_EnableDisableSingle() {
		$this->page->login()->open('host_discovery.php?&hostid='.self::HOST_ID);
		$table = $this->query('class:list-table')->asTable()->one();
		$row = $table->findRow('Name', 'Discovery rule 2');

		// Clicking Enabled/Disabled link
		$discovery_status = ['Enabled' => 1, 'Disabled' => 0];
		foreach ($discovery_status as $action => $expected_status) {
			$row->query('link', $action)->one()->click();
			$status = CDBHelper::getValue('SELECT status FROM items WHERE name='.zbx_dbstr('Discovery rule 2').' and hostid='
				.self::HOST_ID);
			$this->assertEquals($expected_status, $status);
			$message_action = ($action === 'Enabled') ? 'disabled' : 'enabled';
			$this->assertEquals('Discovery rule '.$message_action, CMessageElement::find()->one()->getTitle());
			$link_color = ($action === 'Enabled') ? 'red' : 'green';
			$this->assertTrue($row->query('xpath://td/a[@class="link-action '.$link_color.'"]')->one()->isPresent());
		}
	}

	public function testPageLowLevelDiscovery_EnableDisableAll() {
		$this->page->login()->open('host_discovery.php?&hostid='.self::HOST_ID);
		$discovery_rule_names = ['Discovery rule 1', 'Discovery rule 2', 'Discovery rule 3'];
		// Press Enable or Disable buttons and check the result.
		foreach (['Disable', 'Enable'] as $action) {
			$this->massChangeStatus($action);
			$expected_status = $action === 'Disable' ? 1 : 0;
			foreach ($discovery_rule_names as $name) {
				$status = CDBHelper::getValue('SELECT status FROM items WHERE name ='.zbx_dbstr($name).
					' and hostid='.self::HOST_ID);
				$this->assertEquals($expected_status, $status);
			}
		}
	}

	public static function getCheckNowData() {
		return [
			[
				[
					'expected' => TEST_GOOD,
					'names' => [
						['Name' => 'Discovery rule 2'],
						['Name' => 'Discovery rule 3']
					],
					'message' => 'Request sent successfully',
					'hostid' => self::HOST_ID
				]
			],
			[
				[
					'expected' => TEST_GOOD,
					'names' => [
						'Name' => 'Discovery rule 2'
					],
					'message' => 'Request sent successfully',
					'hostid' => self::HOST_ID
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'names' => [
						'Name' => 'Discovery rule 2'
					],
					'disabled' => true,
					'message' => 'Cannot send request',
					'details' => 'Cannot send request: discovery rule is disabled.',
					'hostid' => self::HOST_ID
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'names' => [
						'Name' => 'Temp Status Discovery'
					],
					'template' => true,
					'hostid' => 10250
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'names' => [
						'Name' => 'Discovery rule 1'
					],
					'message' => 'Cannot send request',
					'details' => 'Cannot send request: wrong discovery rule type.',
					'hostid' => self::HOST_ID
				]
			]
		];
	}

	/**
	 * @backup items
	 *
	 * @dataProvider getCheckNowData
	 */
	public function testPageLowLevelDiscovery_CheckNow($data) {
		$this->page->login()->open('host_discovery.php?hostid='.$data['hostid']);
		// Enabe all LLDs, so Check now can be send successfully.
		$this->massChangeStatus('Enable');
		$this->selectTableRows($data['names']);

		if (CTestArrayHelper::get($data, 'disabled')) {
			$this->query('button:Disable')->one()->click();
			$this->page->acceptAlert();
			$this->selectTableRows($data['names']);
		}

		if (CTestArrayHelper::get($data, 'template', false)) {
			$this->assertFalse($this->query('button:Check now')->one()->isEnabled());
		}
		else {
			$this->query('button:Check now')->one()->click();
			$this->assertMessage($data['expected'], $data['message'], CTestArrayHelper::get($data, 'details'));
		}
	}

	public static function getDeleteAllButtonData() {
		return [
			[
				[
					'expected' => TEST_BAD,
					'names' => [
						'Name' => 'Template ZBX6663 Second: DiscoveryRule ZBX6663 Second'
					],
					'message' => 'Cannot delete discovery rules',
					'details' => 'Cannot delete templated items.',
					'hostid' => 50001
				]
			],
			[
				[
					'expected' => TEST_GOOD,
					'names' => [
						'Name' => 'Discovery rule 1'
					],
					'message' => 'Discovery rules deleted',
					'hostid' => self::HOST_ID
				]
			]
		];
	}

	/**
	 * @dataProvider getDeleteAllButtonData
	 */
	public function testPageLowLevelDiscovery_DeleteAllButton($data) {
		$this->page->login()->open('host_discovery.php?hostid='.$data['hostid']);
		// Enabe all LLDs, so Check now can be send successfully.
		$this->selectTableRows($data['names']);
		$this->query('button:Delete')->one()->click();
		$this->page->acceptAlert();
		$this->assertMessage($data['expected'], $data['message'], CTestArrayHelper::get($data, 'details'));
	}

	private function massChangeStatus($action) {
		$this->query('id:all_items')->asCheckbox()->one()->check();
		$this->query('button:'.$action)->one()->click();
		$this->page->acceptAlert();
		$this->assertEquals('Discovery rules '.lcfirst($action).'d', CMessageElement::find()->one()->getTitle());
	}
}
