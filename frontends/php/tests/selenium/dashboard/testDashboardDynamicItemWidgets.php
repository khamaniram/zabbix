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

require_once dirname(__FILE__) . '/../../include/CWebTest.php';

/**
 * @backup profiles
 */
class testDashboardDynamicItemWidgets extends CWebTest {

	public static function getWidgetsData() {
		return [
			// Default host and group state.
			[
				[
					'filter' => [
						'Group' => 'all',
						'Host' => 'not selected'
					],
					'widgets' => [
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I2'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I1'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I2'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G2 (I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G1 (I1)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G2 (I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G3 (I1 and I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1 G4 (H1I1 and H3I1)'],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I2',
							'expected' => ['Dynamic widgets H1I2' => '12']
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I1',
							'expected' => ['Dynamic widgets H1I1' => '11']
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I2',
							'expected' => ['Dynamic widgets H1I2' => '12']
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: 2 items',
							'expected' => [
								'Dynamic widgets H1I1' => '11',
								'Dynamic widgets H1I2' => '12'
							]
						],
						[
							'type' => 'URL',
							'header' => 'Dynamic URL',
							'empty' => true
						]
					]
				]
			],
			// Hosts.
			[
				[
					'filter' => [
						'Group' => 'all',
						'Host' => 'Dynamic widgets H1'
					],
					'widgets' => [
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I2'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I1'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I2'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G2 (I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G1 (I1)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G2 (I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G3 (I1 and I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G4 (H1I1 and H3I1)'],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I2',
							'expected' => ['Dynamic widgets H1I2' => '12']
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I1',
							'expected' => ['Dynamic widgets H1I1' => '11']
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I2',
							'expected' => ['Dynamic widgets H1I2' => '12']
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: 2 items',
							'expected' => [
								'Dynamic widgets H1I1' => '11',
								'Dynamic widgets H1I2' => '12'
							]
						],
						[
							'type' => 'URL',
							'header' => 'Dynamic URL',
							'host' => 'Dynamic widgets H1'
						]
					]
				]
			],
			[
				[
					'filter' => [
						'Group' => 'all',
						'Host' => 'Dynamic widgets H2'
					],
					'widgets' => [
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I2'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H2: Dynamic widgets H2I1'],
						['type' => 'Graph (classic)', 'header' => 'Graph (classic)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G2 (I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H2: Dynamic widgets H1 G1 (I1)'],
						['type' => 'Graph (classic)', 'header' => 'Graph (classic)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H2: Dynamic widgets H1 G3 (I1 and I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H2: Dynamic widgets H1 G4 (H1I1 and H3I1)'],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I2',
							'expected' => ['Dynamic widgets H1I2' => '12']
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H2: Dynamic widgets H2I1',
							'expected' => ['Dynamic widgets H2I1' => '21']
						],
						[
							'type' => 'Plain text',
							'header' => 'Plain text'
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H2: Dynamic widgets H2I1',
							'expected' => ['Dynamic widgets H2I1' => '21']
						],
						[
							'type' => 'URL',
							'header' => 'Dynamic URL',
							'host' => 'Dynamic widgets H2'
						]
					]
				]
			],
			[
				[
					'filter' => [
						'Group' => 'all',
						'Host' => 'Dynamic widgets H3'
					],
					'widgets' => [
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I2'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H3: Dynamic widgets H3I1'],
						['type' => 'Graph (classic)', 'header' => 'Graph (classic)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G2 (I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H3: Dynamic widgets H1 G1 (I1)'],
						['type' => 'Graph (classic)', 'header' => 'Graph (classic)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H3: Dynamic widgets H1 G3 (I1 and I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H3: Dynamic widgets H1 G4 (H1I1 and H3I1)'],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I2',
							'expected' => ['Dynamic widgets H1I2' => '12']
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H3: Dynamic widgets H3I1',
							'expected' => ['Dynamic widgets H3I1' => '31']
						],
						[
							'type' => 'Plain text',
							'header' => 'Plain text'
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H3: Dynamic widgets H3I1',
							'expected' => ['Dynamic widgets H3I1' => '31']
						],
						[
							'type' => 'URL',
							'header' => 'Dynamic URL',
							'host' => 'Dynamic widgets H3'
						]
					]
				]
			],
			[
				[
					'filter' => [
						'Group' => 'all',
						'Host' => 'Host for suppression'
					],
					'widgets' => [
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I2'],
						['type' => 'Graph (classic)', 'header' => 'Graph (classic)'],
						['type' => 'Graph (classic)', 'header' => 'Graph (classic)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G2 (I2)'],
						['type' => 'Graph (classic)', 'header' => 'Graph (classic)'],
						['type' => 'Graph (classic)', 'header' => 'Graph (classic)'],
						['type' => 'Graph (classic)', 'header' => 'Graph (classic)'],
						['type' => 'Graph (classic)', 'header' => 'Graph (classic)'],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I2',
							'expected' => ['Dynamic widgets H1I2' => '12']
						],
						[
							'type' => 'Plain text',
							'header' => 'Plain text'
						],
						[
							'type' => 'Plain text',
							'header' => 'Plain text'
						],
						[
							'type' => 'Plain text',
							'header' => 'Plain text'
						],
						[
							'type' => 'URL',
							'header' => 'Dynamic URL',
							'host' => 'Host for suppression'
						]
					]
				]
			],
			// Groups.
			[
				[
					'filter' => [
						'Group' => 'Dynamic widgets HG1 (H1 and H2)',
						'Host' => 'not selected'
					],
					'widgets' => [
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I2'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I1'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I2'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G2 (I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G1 (I1)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G2 (I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G3 (I1 and I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1 G4 (H1I1 and H3I1)'],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I2',
							'expected' => ['Dynamic widgets H1I2' => '12']
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I1',
							'expected' => ['Dynamic widgets H1I1' => '11']
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I2',
							'expected' => ['Dynamic widgets H1I2' => '12']
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: 2 items',
							'expected' => [
								'Dynamic widgets H1I1' => '11',
								'Dynamic widgets H1I2' => '12'
							]
						],
						[
							'type' => 'URL',
							'header' => 'Dynamic URL',
							'empty' => true
						]
					]
				]
			],
			[
				[
					'filter' => [
						'Group' => 'Dynamic widgets HG2 (H3)',
						'Host' => 'not selected'
					],
					'widgets' => [
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I2'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I1'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I2'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G2 (I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G1 (I1)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G2 (I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G3 (I1 and I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1 G4 (H1I1 and H3I1)'],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I2',
							'expected' => ['Dynamic widgets H1I2' => '12']
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I1',
							'expected' => ['Dynamic widgets H1I1' => '11']
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I2',
							'expected' => ['Dynamic widgets H1I2' => '12']
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: 2 items',
							'expected' => [
								'Dynamic widgets H1I1' => '11',
								'Dynamic widgets H1I2' => '12'
							]
						],
						[
							'type' => 'URL',
							'header' => 'Dynamic URL',
							'empty' => true
						]
					]
				]
			],
			[
				[
					'filter' => [
						'Group' => 'Another group to check Overview',
						'Host' => 'not selected'
					],
					'widgets' => [
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I2'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I1'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I2'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G2 (I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G1 (I1)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G2 (I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G3 (I1 and I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1 G4 (H1I1 and H3I1)'],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I2',
							'expected' => ['Dynamic widgets H1I2' => '12']
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I1',
							'expected' => ['Dynamic widgets H1I1' => '11']
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I2',
							'expected' => ['Dynamic widgets H1I2' => '12']
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: 2 items',
							'expected' => [
								'Dynamic widgets H1I1' => '11',
								'Dynamic widgets H1I2' => '12'
							]
						],
						[
							'type' => 'URL',
							'header' => 'Dynamic URL',
							'empty' => true
						]
					]
				]
			],
			// Group and host.
			[
				[
					'filter' => [
						'Group' => 'Dynamic widgets HG1 (H1 and H2)',
						'Host' => 'Dynamic widgets H1'
					],
					'widgets' => [
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I2'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I1'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I2'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G2 (I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G1 (I1)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G2 (I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G3 (I1 and I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G4 (H1I1 and H3I1)'],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I2',
							'expected' => ['Dynamic widgets H1I2' => '12']
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I1',
							'expected' => ['Dynamic widgets H1I1' => '11']
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I2',
							'expected' => ['Dynamic widgets H1I2' => '12']
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: 2 items',
							'expected' => [
								'Dynamic widgets H1I1' => '11',
								'Dynamic widgets H1I2' => '12'
							]
						],
						[
							'type' => 'URL',
							'header' => 'Dynamic URL',
							'host' => 'Dynamic widgets H1'
						]
					]
				]
			],
			[
				[
					'filter' => [
						'Group' => 'Dynamic widgets HG1 (H1 and H2)',
						'Host' => 'Dynamic widgets H2'
					],
					'widgets' => [
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I2'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H2: Dynamic widgets H2I1'],
						['type' => 'Graph (classic)', 'header' => 'Graph (classic)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G2 (I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H2: Dynamic widgets H1 G1 (I1)'],
						['type' => 'Graph (classic)', 'header' => 'Graph (classic)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H2: Dynamic widgets H1 G3 (I1 and I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H2: Dynamic widgets H1 G4 (H1I1 and H3I1)'],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I2',
							'expected' => ['Dynamic widgets H1I2' => '12']
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H2: Dynamic widgets H2I1',
							'expected' => ['Dynamic widgets H2I1' => '21']
						],
						[
							'type' => 'Plain text',
							'header' => 'Plain text'
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H2: Dynamic widgets H2I1',
							'expected' => ['Dynamic widgets H2I1' => '21']
						],
						[
							'type' => 'URL',
							'header' => 'Dynamic URL',
							'host' => 'Dynamic widgets H2'
						]
					]
				]
			],
			[
				[
					'filter' => [
						'Group' => 'Dynamic widgets HG2 (H3)',
						'Host' => 'Dynamic widgets H3'
					],
					'widgets' => [
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I2'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H3: Dynamic widgets H3I1'],
						['type' => 'Graph (classic)', 'header' => 'Graph (classic)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G2 (I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H3: Dynamic widgets H1 G1 (I1)'],
						['type' => 'Graph (classic)', 'header' => 'Graph (classic)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H3: Dynamic widgets H1 G3 (I1 and I2)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H3: Dynamic widgets H1 G4 (H1I1 and H3I1)'],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I2',
							'expected' => ['Dynamic widgets H1I2' => '12']
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H3: Dynamic widgets H3I1',
							'expected' => ['Dynamic widgets H3I1' => '31']
						],
						[
							'type' => 'Plain text',
							'header' => 'Plain text'
						],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H3: Dynamic widgets H3I1',
							'expected' => ['Dynamic widgets H3I1' => '31']
						],
						[
							'type' => 'URL',
							'header' => 'Dynamic URL',
							'host' => 'Dynamic widgets H3'
						]
					]
				]
			],
			[
				[
					'filter' => [
						'Group' => 'Host group for suppression',
						'Host' => 'Host for suppression'
					],
					'widgets' => [
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1I2'],
						['type' => 'Graph (classic)', 'header' => 'Graph (classic)'],
						['type' => 'Graph (classic)', 'header' => 'Graph (classic)'],
						['type' => 'Graph (classic)', 'header' => 'Dynamic widgets H1: Dynamic widgets H1 G2 (I2)'],
						['type' => 'Graph (classic)', 'header' => 'Graph (classic)'],
						['type' => 'Graph (classic)', 'header' => 'Graph (classic)'],
						['type' => 'Graph (classic)', 'header' => 'Graph (classic)'],
						['type' => 'Graph (classic)', 'header' => 'Graph (classic)'],
						[
							'type' => 'Plain text',
							'header' => 'Dynamic widgets H1: Dynamic widgets H1I2',
							'expected' => ['Dynamic widgets H1I2' => '12']
						],
						[
							'type' => 'Plain text',
							'header' => 'Plain text'
						],
						[
							'type' => 'Plain text',
							'header' => 'Plain text'
						],
						[
							'type' => 'Plain text',
							'header' => 'Plain text'
						],
						[
							'type' => 'URL',
							'header' => 'Dynamic URL',
							'host' => 'Host for suppression'
						]
					]
				]
			]
		];
	}

	/**
	 * @on-before createTestFile
	 * @on-after removeTestFile
	 *
	 * @dataProvider getWidgetsData
	 */
	public function testDashboardDynamicItemWidgets_Layout($data) {
		$this->page->login()->open('zabbix.php?action=dashboard.view&dashboardid=105');
		$dashboard = CDashboardElement::find()->one();
		$filter = $dashboard->getControls()->waitUntilVisible();
		$filter->query('name:groupid')->asDropdown()->one()->select($data['filter']['Group']);
		$filter->query('name:hostid')->asDropdown()->one()->select($data['filter']['Host']);
		$this->page->waitUntilReady();
		$widgets = $dashboard->getWidgets();
		$this->assertEquals(count($data['widgets']), $widgets->count());

		foreach ($data['widgets'] as $key => $expected) {
			$widget = $widgets->get($key);
			$widget_content = $widget->getContent();
			$this->assertEquals($expected['header'], $widget->getHeaderText());

			// Check widget empty content, because the host group or/and host doesn't match dynamic option criteria.
			if ($expected['header'] === 'Graph (classic)' || $expected['header'] === 'Plain text'
					|| CTestArrayHelper::get($expected, 'empty', false)) {
				$content = $widget_content->query('class:nothing-to-show')->one()->getText();
				$message = ($expected['type'] === 'URL')
						? 'No host selected.'
						: 'No permissions to referred object or it does not exist!';
				$this->assertEquals($message, $content);
				continue;
			}

			// Check widget content when the host group or/and host match dynamic option criteria.
			$this->assertFalse($widget_content->query('class:nothing-to-show')->one(false)->isValid());
			switch ($expected['type']) {
				case 'Plain text':
					$data = $widget_content->asTable()->index('Name');
					foreach ($expected['expected'] as $item => $value) {
						$row = $data[$item];
						$this->assertEquals($value, $row['Value']);
					}
					break;

				case 'URL':
					$this->page->switchTo($widget_content->query('id:iframe')->one());
					$params = json_decode($this->query('xpath://body')->one()->getText(), true);
					$this->assertEquals($expected['host'], $params['name']);
					$this->page->switchTo();
					break;
			}
		}
	}

	public function createTestFile() {
		if (file_put_contents(PHPUNIT_BASEDIR.'/frontends/php/iframe.php', '<?php echo json_encode($_GET);') === false) {
			throw new Exception('Failed to create iframe test file.');
		}
	}

	public function removeTestFile() {
		@unlink(PHPUNIT_BASEDIR.'/frontends/php/iframe.php');
	}
}
