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

require_once dirname(__FILE__).'/../../include/CWebTest.php';
require_once dirname(__FILE__).'/../../include/helpers/CDataHelper.php';

/**
 * @backup widget
 * @backup profiles
 */
class testDashboardProblemsBySeverityWidget extends CWebTest {

	/**
	 * Id of the dashboard that is created within this test specifically for the update scenario.
	 *
	 * @var integer
	 */
	protected static $dashboardid;

	/*
	 * SQL query to get widget and widget_field tables to compare hash values, but without widget_fieldid
	 * because it can change.
	 */
	private $sql = 'SELECT wf.widgetid, wf.type, wf.name, wf.value_int, wf.value_str, wf.value_groupid, wf.value_hostid,'.
			' wf.value_itemid, wf.value_graphid, wf.value_sysmapid, w.widgetid, w.dashboardid, w.type, w.name, w.x, w.y,'.
			' w.width, w.height'.
			' FROM widget_field wf'.
			' INNER JOIN widget w'.
			' ON w.widgetid=wf.widgetid ORDER BY wf.widgetid, wf.name, wf.value_int, wf.value_str, wf.value_groupid,'.
			' wf.value_itemid, wf.value_graphid';

	public function getCreateWidgetData() {
		return [
			// Create a widget with default values.
			[
				[
					'fields' => [
						'Type' => 'Problems by severity'
					],
					'check' => 'pop-up'
				]
			],
			// Create a widget with selected 'show latest values' option and removed 'show timeline' option.
			[
				[
					'fields' => [
						'Type' => 'Problems by severity',
						'Name' => 'Show latest values and timeline',
						'Refresh interval' => 'Default (1 minute)',
						'Show latest values' => true,
						'Show timeline' => false
					],
					'check' => 'pop-up'
				]
			],
			// Create a widget that shows only hosts with problems with problem filtering by their severity.
			[
				[
					'fields' => [
						'Type' => 'Problems by severity',
						'Name' => 'Show only hosts with problems filtered by severity',
						'Refresh interval' => '1 minute',
						'Hide groups without problems' => true,
						'Severity' => ['Disaster', 'Warning', 'Information']
					],
					'expected' => [
						'Group to check Overview' => [
							'Disaster' => '1',
							'Warning' => '1',
							'Information' => '1'
						],
						'Zabbix servers' => [
							'Warning' => '5'
						]
					]
				]
			],
			// Create a widget with selected 'show suppressed problems' option.
			[
				[
					'fields' => [
						'Type' => 'Problems by severity',
						'Name' => 'Show suppressed problems',
						'Show suppressed problems' => true
					]
				]
			],
			// Create a widget with selected 'show suppressed problems' option that shows only hosts with problems.
			[
				[
					'fields' => [
						'Type' => 'Problems by severity',
						'Name' => 'Show only for hosts with problems + show suppressed problems',
						'Show suppressed problems' => true,
						'Hide groups without problems' => true,
						'Problem display' => 'Separated'
					],
					'expected' => [
						'Another group to check Overview' => [
							'Average' => 1
						],
						'Group to check Overview' => [
							'Disaster' => "1\nof 1",
							'High' => "1\nof 1",
							'Average' => "1\nof 2",
							'Warning' => "1\nof 1",
							'Information' => '1',
							'Not classified' => "1\nof 1"
						],
						'Host group for suppression' => [
							'Average' => "1\nof 1"
						],
						'Host group for tag permissions' => [
							'Not classified' => "2\nof 2"
						],
						'Zabbix servers' => [
							'Average' => "1\nof 1",
							'Warning' => "5\nof 5"
						]
					]
				]
			],
			// Create a widget with 'Zabbix servers' and 'Another group to check Overview' host group problems.
			[
				[
					'fields' => [
						'Type' => 'Problems by severity',
						'Name' => 'Show only "Zabbix servers" and "Another group to check Overview" problems',
						'Host groups' => ['Zabbix servers', 'Another group to check Overview'],
						'Show latest values' => true
					],
					'expected' => [
						'Another group to check Overview' => [
							'Average' => '1'
						],
						'Zabbix servers' => [
							'Average' => '1',
							'Warning' => '5'
						]
					],
					'check' => 'pop-up'
				]
			],
			// Create a widget that excludes 'Zabbix servers' host group.
			[
				[
					'fields' => [
						'Type' => 'Problems by severity',
						'Name' => 'Exclude "Zabbix servers"',
						'Exclude host groups' => ['Zabbix servers', 'Empty group', 'Group to check Overview']
					]
				]
			],
			// Create a widget that shows only '1_Hos_to_check_Monitoring_Overview' host problems.
			[
				[
					'fields' => [
						'Type' => 'Problems by severity',
						'Name' => 'Display only "1_Host_to_check_Monitoring_Overview"',
						'Problem display' => 'Separated',
						'Severity' => ['Disaster', 'High',	'Average', 'Warning', 'Information', 'Not classified'],
						'Hosts' => [
							'values' => ['1_Host_to_check_Monitoring_Overview'],
							'context' => 'Group to check Overview'
						]
					],
					'expected' => [
						'Group to check Overview' => [
							'Disaster' => "1\nof 1",
							'High' => "1\nof 1",
							'Average' => "1\nof 1",
							'Warning' => "1\nof 1",
							'Information' => '1',
							'Not classified' => "1\nof 1"
						]
					]
				]
			],
			// Create a widget that shows only 'Third test trigger with tag priority' problem.
			[
				[
					'fields' => [
						'Type' => 'Problems by severity',
						'Name' => 'Display only "Third test trigger with tag priority" problem',
						'Problem' => 'Third test trigger with tag priority',
						'Problem display' => 'Separated'
					],
					'expected' => [
						'Zabbix servers' => [
							'Warning' => "1\nof 1"
						]
					]
				]
			],
			// Create a widget that shows only problems that contain 'Test trigger with tag'.
			[
				[
					'fields' => [
						'Type' => 'Problems by severity',
						'Name' => 'Display problems containing "Test trigger with tag" string',
						'Problem' => 'Test trigger with tag'
					],
					'expected' => [
						'Zabbix servers' => [
							'Warning' => '5'
						]
					]
				]
			],
			// Create a widget that shows only unaknowledged problems.
			[
				[
					'fields' => [
						'Type' => 'Problems by severity',
						'Name' => 'Display only unacknowledged problems',
						'Problem display' => 'Unacknowledged only'
					],
					'expected' => [
						'Group to check Overview' => [
							'Disaster' => '1',
							'High' => '1',
							'Average' => '1',
							'Warning' => '1',
							'Not classified' => '1'
						],
						'Host group for tag permissions' => [
							'Not classified' => '2'
						],
						'Zabbix servers' => [
							'Average' => '1',
							'Warning' => '5'
						]
					]
				]
			]
		];
	}

	/**
	 * @dataProvider getCreateWidgetData
	 */
	public function testDashboardProblemsBySeverityWidget_Create($data) {
		$this->page->login()->open('zabbix.php?action=dashboard.view&dashboardid=104');
		$dashboard = CDashboardElement::find()->one();
		$old_widget_count = $dashboard->getWidgets()->count();

		// Add a widget.
		$dialogue = $dashboard->edit()->addWidget();
		$form = $dialogue->asForm();
		$header = CTestArrayHelper::get($data['fields'], 'Name', 'Problems by severity');
		$this->fillFormAndSaveDashboard($dashboard, $form, $data, $header);
		$widget = $dashboard->getWidget($header);

		$this->checkDashboardMessage();
		$this->assertEquals($old_widget_count + 1, $dashboard->getWidgets()->count());
		$this->checkWidgetContent($data, $widget);

		// Check the content of details hintbox for Host "ЗАББИКС Сервер" and severity "Average" if needed.
		if (CTestArrayHelper::get($data, 'check', 'nothing') === 'pop-up') {
			$this->checkPopupContent($data, $widget);
		}
	}

	/**
	 * Function used to create a dashboard with widgets required for the Update scenario.
	 */
	public function prepareUpdateData() {
		// Form an array with widget configuration
		$widgets = [];
		$id = 1;
		for ($y = 0; $y <= 25; $y += 5) {
			for ($x = 0; $x <= 6; $x += 6) {
				if ($id === 12) {
					break 2;
				}
				$widgets[] = [
					'type' => 'problemsbysv',
					'name' => 'Reference widget '.$id,
					'x' => $x,
					'y' => $y,
					'width' => 6,
					'height' => 5
				];

				$id++;
			}
		}

		// Create dashboard
		$response = CDataHelper::call('dashboard.create', [
			'name' => 'Problems by severity update dashboard',
			'widgets' => array_values($widgets)
		]);

		$this->assertArrayHasKey('dashboardids', $response);
		self::$dashboardid = $response['dashboardids'][0];
	}

	public function getUpdateWidgetData() {
		return [
			// Update widget to have a default name.
			[
				[
					'widget to update' => 'Reference widget 1',
					'fields' => [
						'Name' => ''
					]
				]
			],
			// Hide host groups without problems and remove timeline.
			[
				[
					'widget to update' => 'Reference widget 2',
					'fields' => [
						'Name' => 'Hide timeline and groupt without problems',
						'Refresh interval' => 'Default (1 minute)',
						'Hide groups without problems' => true,
						'Show timeline' => false
					],
					'expected' => [
						'Another group to check Overview' => [
							'Average' => 1
						],
						'Group to check Overview' => [
							'Disaster' => '1',
							'High' => '1',
							'Average' => '2',
							'Warning' => '1',
							'Information' => '1',
							'Not classified' => '1'
						],
						'Host group for tag permissions' => [
							'Not classified' => '2'
						],
						'Zabbix servers' => [
							'Average' => '1',
							'Warning' => '5'
						]
					],
					'check' => 'pop-up'
				]
			],
			// Show only average problems including suppressed ones, problem display - separated, exclude hostgroups without problems.
			[
				[
					'widget to update' => 'Reference widget 3',
					'fields' => [
						'Name' => 'Show only average problems including suppressed ones',
						'Hide groups without problems' => true,
						'Show suppressed problems' => true,
						'Problem display' => 'Separated',
						'Severity' => ['Average']
					],
					'expected' => [
						'Another group to check Overview' => [
							'Average' => '1'
						],
						'Group to check Overview' => [
							'Average' => "1\nof 2"
						],
						'Host group for suppression' => [
							'Average' => "1\nof 1"
						],
						'Zabbix servers' => [
							'Average' => "1\nof 1"
						]
					]
				]
			],
			// Update widget to display only unaknowledged problems and to show latest values.
			[
				[
					'widget to update' => 'Reference widget 4',
					'fields' => [
						'Name' => 'Display only unacknowledged problems',
						'Problem display' => 'Unacknowledged only',
						'Show latest values' => true
					],
					'expected' => [
						'Group to check Overview' => [
							'Disaster' => '1',
							'High' => '1',
							'Average' => '1',
							'Warning' => '1',
							'Not classified' => '1'
						],
						'Host group for tag permissions' => [
							'Not classified' => '2'
						],
						'Zabbix servers' => [
							'Average' => '1',
							'Warning' => '5'
						]
					],
					'check' => 'pop-up'
				]
			],
			// Update the widget to return only "Group to check Overview" hostgroup problems.
			[
				[
					'widget to update' => 'Reference widget 5',
					'fields' => [
						'Name' => 'Show only problems of hostgroup Group to check Overview',
						'Host groups' => 'Group to check Overview'
					],
					'expected' => [
						'Group to check Overview' => [
							'Disaster' => '1',
							'High' => '1',
							'Average' => '2',
							'Warning' => '1',
							'Information' => '1',
							'Not classified' => '1'
						]
					]
				]
			],
			// Empty widget output: return problems of 'Zabbix servers' hostroup and a host that doesn't belong to it.
			[
				[
					'widget to update' => 'Reference widget 6',
					'fields' => [
						'Name' => 'Return "Zabbix servers" and "Another group to check Overview" problems',
						'Host groups' => ['Zabbix servers'],
						'Hosts' => [
							'values' => ['Empty host'],
							'context' => 'Empty group'
						]
					],
					'empty' => true
				]
			],
			// Update widget to exclude 'Group to check Overview' host group.
			[
				[
					'widget to update' => 'Reference widget 7',
					'fields' => [
						'Name' => 'Exclude "Group to check Overview"',
						'Exclude host groups' => ['Group to check Overview']
					],
					'expected' => [
						'Another group to check Overview' => [
							'Average' => '1'
						],
						'Host group for tag permissions' =>[
							'Not classified' => '2'
						],
						'Zabbix servers' => [
							'Average' => '1',
							'Warning' => '5'
						]
					]
				]
			],
			// Update widget to return problems of 'ЗАББИКС Сервер' host.
			[
				[
					'widget to update' => 'Reference widget 8',
					'fields' => [
						'Name' => 'Return "ЗАББИКС Сервер" problems',
						'Hosts' => [
							'values' => ['ЗАББИКС Сервер'],
							'context' => 'Zabbix servers'
						]
					],
					'expected' => [
						'Zabbix servers' => [
							'Average' => '1',
							'Warning' => '5'
						]
					]
				]
			],
			// Empty widget output: problems of "ЗАББИКС Сервер" host with excluded "Zabbix servers" hostgroup.
			[
				[
					'widget to update' => 'Reference widget 9',
					'fields' => [
						'Name' => 'Display ЗАББИКС Сервер problems with excluded "Zabbix servers"',
						'Exclude host groups' => ['Zabbix servers'],
						'Severity' => ['Disaster', 'High',	'Average', 'Warning', 'Information', 'Not classified'],
						'Hosts' => [
							'values' => ['ЗАББИКС Сервер'],
							'context' => 'Zabbix servers'
						]
					],
					'empty' => true
				]
			],
			// Update widget to show a non existing problem.
			[
				[
					'widget to update' => 'Reference widget 10',
					'fields' => [
						'Name' => 'No problems should be returned',
						'Problem' => 'Please place Your problem name here'
					],
					'expected' => []
				]
			],
			// Update widget to show only warning and information problems that contain '_trigger_'.
			[
				[
					'widget to update' => 'Reference widget 11',
					'fields' => [
						'Name' => 'Display only warning and information problems containing "_trigger_',
						'Problem' => '_trigger_',
						'Severity' => ['Warning', 'Information']
					],
					'expected' => [
						'Group to check Overview' => [
							'Warning' => '1',
							'Information' => '1'
						]
					]
				]
			]
		];
	}

	/**
	 * @on-before-once prepareUpdateData
	 * @dataProvider getUpdateWidgetData
	 */
	public function testDashboardProblemsBySeverityWidget_Update($data) {
		$this->page->login()->open('zabbix.php?action=dashboard.view&dashboardid='.self::$dashboardid);
		$dashboard = CDashboardElement::find()->one();
		$dashboard->edit();
		$form = $dashboard->getWidget($data['widget to update'])->edit();

		// Attempt to update the widget.
		$header = ($data['fields']['Name'] === '') ? 'Problems by severity' : $data['fields']['Name'];

		$this->fillFormAndSaveDashboard($dashboard, $form, $data, $header);
		$widget = $dashboard->getWidget($header);

		$this->checkDashboardMessage();
		$this->checkWidgetContent($data, $widget);

		// Check the content of details hintbox for Host "ЗАББИКС Сервер" and severity "Average" if needed.
		if (CTestArrayHelper::get($data, 'check', 'nothing') === 'pop-up') {
			$this->checkPopupContent($data, $widget);
		}
	}

	public function testDashboardProblemsBySeverityWidget_SimpleUpdate() {
		$old_hash = CDBHelper::getHash($this->sql);

		// Open a dashboard widget and then save it without applying any changes
		$this->page->login()->open('zabbix.php?action=dashboard.view&dashboardid=104');
		$dashboard = CDashboardElement::find()->one();
		$dashboard->edit();
		$form = $dashboard->getWidget('Reference widget')->edit();
		$form->submit();
		$this->page->waitUntilReady();

		$dashboard->getWidget('Reference widget');
		$dashboard->save();

		// Check that Dashboard has been saved and that there are no changes made to the widgets.
		$this->checkDashboardMessage();
		$this->assertEquals($old_hash, CDBHelper::getHash($this->sql));
	}

	public function getCancelActionsData() {
		return [
			// Cancel update widget.
			[
				[
					'existing_widget' => 'Reference widget',
					'save_widget' => true,
					'save_dashboard' => false
				]
			],
			[
				[
					'existing_widget' => 'Reference widget',
					'save_widget' => false,
					'save_dashboard' => true
				]
			],
			// Cancel create widget.
			[
				[
					'save_widget' => true,
					'save_dashboard' => false
				]
			],
			[
				[
					'save_widget' => false,
					'save_dashboard' => true
				]
			]
		];
	}

	/**
	 * @dataProvider getCancelActionsData
	 */
	public function testDashboardProblemsBySeverityWidget_Cancel($data) {
		$old_hash = CDBHelper::getHash($this->sql);

		$this->page->login()->open('zabbix.php?action=dashboard.view&dashboardid=104');
		$dashboard = CDashboardElement::find()->one()->edit();

		// Start updating or creating a widget.
		if (CTestArrayHelper::get($data, 'existing_widget', false)) {
			$widget = $dashboard->getWidget($data['existing_widget']);
			$form = $widget->edit();
		}
		else {
			$overlay = $dashboard->addWidget();
			$form = $overlay->asForm();
			$form->getField('Type')->fill('Problems by severity');
			$widget = $dashboard->getWidgets()->last();
		}
		$form->getField('Name')->fill('Widget to be cancelled');

		// Save or cancel widget.
		if (CTestArrayHelper::get($data, 'save_widget', false)) {
			$form->submit();
			$this->page->waitUntilReady();
			// Check that changes took place on the unsaved dashboard.
			$this->assertTrue($dashboard->getWidget('Widget to be cancelled')->isVisible());
		}
		else {
			$this->query('button:Cancel')->one()->click();
			// Check that widget changes wasn't took place after pressing "Cancel".
			if (CTestArrayHelper::get($data, 'existing_widget', false)) {
				$this->assertNotEquals('Widget to be cancelled', $widget->waitUntilReady()->getHeaderText());
			}
			else {
				// If test fails and widget isn't canceled, need to wait until widget appears on the dashboard.
				sleep(5);
				if ($widget->getID() !== $dashboard->getWidgets()->last()->getID()) {
					$this->fail('New widget was added after pressing "Cancel"');
				}
			}
		}
		// Save or cancel dashboard update.
		if (CTestArrayHelper::get($data, 'save_dashboard', false)) {
			$dashboard->save();
		}
		else {
			$dashboard->cancelEditing();
		}
		// Confirm that no changes were made to the widget.
		$this->assertEquals($old_hash, CDBHelper::getHash($this->sql));
	}

	public function testDashboardProblemsBySeverityWidget_Delete() {
		$name = 'Reference PBS widget to delete';

		$this->page->login()->open('zabbix.php?action=dashboard.view&dashboardid=104');
		$dashboard = CDashboardElement::find()->one()->edit();
		$widget = $dashboard->getWidget($name);
		$dashboard->deleteWidget($name);
		$this->page->waitUntilReady();

		$dashboard->save();
		// Check that Dashboard has been saved
		$this->checkDashboardMessage();
		// Confirm that widget is not present on dashboard.
		$this->assertEquals(0, $dashboard->query('xpath:.//div[contains(@class, "dashbrd-grid-widget-head")]/h4[text()='.
				CXPathHelper::escapeQuotes($name).']')->count());
		$sql = 'SELECT * FROM widget_field wf LEFT JOIN widget w ON w.widgetid=wf.widgetid'.
				' WHERE w.name = '.zbx_dbstr($name);
		$this->assertEquals(0, CDBHelper::getCount($sql));
	}

	private function fillFormAndSaveDashboard($dashboard, $form, $data, $header) {
		$form->fill($data['fields']);
		$form->submit();
		$this->query('id:overlay_bg')->waitUntilNotVisible();
		$this->page->waitUntilReady();
		$dashboard->getWidget($header);
		sleep(1);
		$dashboard->save();
	}

	private function checkWidgetContent($data, $widget) {
		$table = $widget->getContent()->asTable();
		// Defining expected results in case if no filtering is applied.
		$default_values = [
			'values' => [
				'Another group to check Overview' => [
					'Average' => '1'
				],
				'Group to check Overview' => [
					'Disaster' => '1',
					'High' => '1',
					'Average' => '2',
					'Warning' => '1',
					'Information' => '1',
					'Not classified' => '1'
				],
				'Host group for tag permissions' => [
					'Not classified' => '2'
				],
				'Zabbix servers' => [
					'Average' => '1',
					'Warning' => '5'
				],
				'Host group for suppression' => [
					'Average' => '1'
				]
			],
			'Severity' => ['Disaster', 'High', 'Average', 'Warning', 'Information', 'Not classified']
		];

		// Check that only chosen severities are returned in the output if 'Severity' filter is used.
		$table_headers = CTestArrayHelper::get($data, 'fields.Severity', false) ? $data['fields']['Severity'] : $default_values['Severity'];
		array_unshift($table_headers, 'Host group');
		$this->assertEquals($table_headers, $table->getHeadersText());

		// Check that nothing is returned in the widget if such outcome is expected.
		if (CTestArrayHelper::get($data, 'empty', false)) {
			$this->assertTrue($widget->query('class:nothing-to-show')->one()->isTextPresent('No data found.'));

			return;
		}

		$content = $table->index('Host group');
		$expected = CTestArrayHelper::get($data, 'expected', $default_values['values']);

		// Remove the suppressed problem from expected results if flag 'Show suppressed problems' is set.
		if (CTestArrayHelper::get($data['fields'], 'Show suppressed problems', false) === false) {
			unset($expected['Host group for suppression']);
		}

		// Remove the host groups included in 'Exclude host groups' from expected results
		if (CTestArrayHelper::get($data['fields'], 'Exclude host groups', false)) {
			foreach ($data['fields']['Exclude host groups'] as $excluded_group) {
				unset($expected[$excluded_group]);
			}
		}

		// Check that only selected host groups are returned when 'Hide groups without values', 'Host groups' or 'Hosts' are specified.
		if (CTestArrayHelper::get($data['fields'], 'Hide groups without problems', false) ||
				array_key_exists('Host groups', $data['fields']) || array_key_exists('Hosts', $data['fields'])) {
			$this->assertEquals(count($expected), count($content));
		}

		// Check the values where result is expected and check that only expected table cells have values.
		foreach ($content as $row => $fields) {
			foreach ($fields as $column => $value) {
				if ($column === 'Host group') {
					continue;
				}
				elseif (array_key_exists($row, $expected) && array_key_exists($column, $expected[$row])) {
					$this->assertEquals($value, $expected[$row][$column]);
				}
				else {
					$this->assertEquals($value, '');
				}
			}
		}
	}

	/*
	 * This function checks problem details hintbox content for Host "ЗАББИКС Сервер" and severity "Average".
	 * Only one such problem should exist.
	 */
	private function checkPopupContent($data, $widget){
		$expected_popup = [
			'fields' => [
				'Time' => '2020-10-23 15:33:48',
				'Host' => 'ЗАББИКС Сервер',
				'Problem' => 'Test trigger to check tag filter on problem page',
				'Ack' => 'No'
			],
			'Tags' => ['Database', 'Service: abc', 'service: abcdef']
		];

		$table = $widget->getContent()->asTable();
		$row = $table->findRow('Host group', 'Zabbix servers');
		$row->query('xpath:.//td[@class="average-bg"]/a')->one()->click();
		$popup = $this->query('xpath://div[@class="overlay-dialogue"]//table')->asTable()->one();
		$this->assertEquals(1, $popup->getRows()->count());

		foreach ($expected_popup['fields'] as $name => $value) {
			$this->assertEquals($value, $popup->getRow(0)->getColumn($name)->getText());
		}
		foreach ($expected_popup['Tags'] as $tag) {
			$tag_array = $popup->getRow(0)->getColumn('Tags')->getText();
			$this->assertContains($tag, $tag_array);
		}
		if (CTestArrayHelper::get($data['fields'], 'Show latest values', false)) {
			$this->assertEquals('*UNKNOWN*', $popup->getRow(0)->getColumn('Latest values')->getText());
		}
		if (CTestArrayHelper::get($data['fields'], 'Show timeline', true)) {
			$this->assertEquals(1, $popup->query('xpath:.//td[@class="timeline-date"]')->all()->count());
		}
	}

	/*
	 * Check dashboard update message.
	 */
	private function checkDashboardMessage() {
		$message = CMessageElement::find()->waitUntilVisible()->one();
		$this->assertTrue($message->isGood());
		$this->assertEquals('Dashboard updated', $message->getTitle());
	}
}
