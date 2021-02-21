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


class CHtmlUrlValidatorTest extends PHPUnit_Framework_TestCase {

	// Expected results are defined assuming that VALIDATE_URI_SCHEMES is enabled (set to be true).
	public function providerValidateURL() {
		return [
			// Valid URLs.
			['http',													true,	true],
			['http://zabbix.com',										true,	true],
			['https://zabbix.com',										true,	true],
			['http://localhost',										true,	true],
			['http://192.168.1.1',										true,	true],
			['http://localhost/file.php',								true,	true],
			['http://localhost/file.html',								true,	true],
			['http://localhost/file',									true,	true],
			['http://hosts.php',										true,	true],
			['http://hello/world/hosts.html?abc=123',					true,	true],
			['http:/hosts.php',											true,	true], // Because we allow tel:1-111-111-1111 and "/hosts.php" is a valid path which falls in same category.
			['http:localost',											true,	true], // Because we allow tel:1-111-111-1111 and "localost" is a valid path which falls in same category.
			['http:/localost',											true,	true], // Because we allow tel:1-111-111-1111 and "/localost" is a valid path which falls in same category.
			['http/',													true,	true], // Because "http/" is a valid relative path.
			['http:/localhost/hosts.php',								true,	true], // Because we allow tel:1-111-111-1111 and "/localhost/hosts.php" is a valid path which falls in same category.
			['http:myhost/hosts.php',									true,	true], // Because we allow tel:1-111-111-1111 and "myhost/hosts.php" is a valid path which falls in same category.
			['localhost',												true,	true],
			['notzabbix.php',											true,	true],
			['hosts.php',												true,	true],
			['hosts.html',												true,	true],
			['/secret/.htaccess',										true,	true], // No file type restrictions.
			['/hosts.php',												true,	true],
			['subdir/hosts.php',										true,	true],
			['subdir/hosts/id/10084',									true,	true],
			['subdir/'.'/100500/',										true,	true], // Comment hook does not allow "//".
			['hosts.php/..',											true,	true],
			['hosts/..php',												true,	true],
			['subdir1/../subdir2/../subdir3/',							true,	true],
			['subdir1/subdir2/hosts.php',								true,	true],
			['192.168.1.1.',											true,	true], // Not a valid IP, but it is accepted as "path".
			['zabbix.php?a=1',											true,	true],
			['adm.images.php?a=1',										true,	true],
			['chart_bar.php?a=1&b=2',									true,	true],
			['mailto:example@example.com',								true,	true],
			['file://localhost/path',									true,	true],
			['tel:1-111-111-1111',										true,	true],
			['ssh://username@hostname:/path ',							true,	true],
			['/chart_bar.php?a=1&b=2',									true,	true],
			['http://localhost:{$PORT}',								true,	true], // Macros allowed.
			['http://{$INVALID!MACRO}',									true,	true], // Macros allowed, but it's not a valid macro.
			['/',														true,	true], // "/" is a valid path to home directory.
			['/../',													true,	true],
			['../',														true,	true],
			['/..',														true,	true],
			['../././not_so_zabbix',									true,	true],
			['jav&#x09;ascript:alert(1];',								true,	true], // "jav" is a valid path with everything else in "fragment".
			['ftp://user@host:21',										true,	true],
			['ftp://somehost',											true,	true],
			['ftp://user@host',											true,	true],
			['{$USER_URL_MACRO}',										true,	true],
			['{$USER_URL_MACRO}?a=1',									true,	true],
			['http://{$USER_URL_MACRO}?a=1',							true,	true],
			['http://{$USER_URL_MACRO}',								true,	true],
			// Macros not allowed.
			['http://{$USER_URL_MACRO}',								false,	true], // Macros not allowed, but it's a host.
			['{$USER_URL_MACRO}',										false,	true], // Macros not allowed, but it's a whole URL.
			['http://localhost/{$USER_URL_MACRO}/',						false,	true], // Macros not allowed, but it's a subdir.
			['http://localhost/hosts.php?hostid={$ID}',					false,	true], // Macros not allowed, but it's in query.
			['http://localhost/hosts.php?hostid=1#comment={$COMMENT}',	false,	true],
			['http://localhost/{NOT_AUSER_MACRO}/',						false,	true], // Macros not allowed, but it's not a macro.
			// Invalid URLs.
			['http:?abc',												true,	false], // Scheme with no host.
			['http:/',													true,	false], // Special case where single "/" is not allowed in path.
			['http://',													true,	false], // url_parse() returs false.
			['http:///',												true,	false], // url_parse() returs false.
			['http:',													true,	false], // Scheme with no host.
			['http://?',												true,	false], // url_parse() returns false.
			['javascript:alert(]',										true,	false], // Invalid scheme.
			['protocol://{$INVALID!MACRO}',								true,	false], // Invalid scheme. Also macro is not valid, but that's secondary.
			['',														true,	false], // Cannot be empty.
			['ftp://user@host:port',									true,	false], // Scheme is allowed, but "port" is not a valid number and url_parse() returs false.
			['vbscript:msgbox(]',										true,	false], // Invalid scheme.
			['notexist://localhost',									true,	false] // Invalid scheme.
		];
	}

	/**
	 * @dataProvider providerValidateURL
	 */
	public function test_validateURL($url, $allow_user_macro, $expected) {
		$this->assertEquals(CHtmlUrlValidator::validate($url, $allow_user_macro), $expected);
	}
}
