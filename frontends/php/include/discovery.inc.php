<?php
/*
** Zabbix
** Copyright (C) 2000-2011 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
**/
?>
<?php
	require_once('include/perm.inc.php');


	function check_right_on_discovery($permission){
		global $USER_DETAILS;

		if( $USER_DETAILS['type'] >= USER_TYPE_ZABBIX_ADMIN ){
			if(count(get_accessible_nodes_by_user($USER_DETAILS, $permission, PERM_RES_IDS_ARRAY)))
				return true;
		}
	return false;
	}

	function svc_default_port($type_int){
		$typePort = array(
			SVC_SSH =>		'22',
			SVC_LDAP =>		'389',
			SVC_SMTP =>		'25',
			SVC_FTP =>		'21',
			SVC_HTTP =>		'80',
			SVC_POP =>		'110',
			SVC_NNTP =>		'119',
			SVC_IMAP =>		'143',
			SVC_AGENT =>	'10050',
			SVC_SNMPv1 =>	'161',
			SVC_SNMPv2 =>	'161',
			SVC_SNMPv3 =>	'161'
		);

		return isset($typePort[$type_int]) ? $typePort[$type_int] : 0;
	}

	function discovery_check_type2str($type=null){
		$discovery_types = array(
			SVC_SSH => S_SSH,
			SVC_LDAP => S_LDAP,
			SVC_SMTP => S_SMTP,
			SVC_FTP => S_FTP,
			SVC_HTTP => S_HTTP,
			SVC_POP => S_POP,
			SVC_NNTP => S_NNTP,
			SVC_IMAP => S_IMAP,
			SVC_TCP => S_TCP,
			SVC_AGENT => S_ZABBIX_AGENT,
			SVC_SNMPv1 => S_SNMPV1_AGENT,
			SVC_SNMPv2 => S_SNMPV2_AGENT,
			SVC_SNMPv3 => S_SNMPV3_AGENT,
			SVC_ICMPPING => S_ICMPPING,
		);

		if(is_null($type)){
			order_result($discovery_types);
			return $discovery_types;
		}
		else if(isset($discovery_types[$type]))
			return $discovery_types[$type];
		else
			return S_UNKNOWN;
	}

	function discovery_check2str($type, $snmp_community, $key_, $port){
		$external_param = '';

		switch($type){
			case SVC_SNMPv1:
			case SVC_SNMPv2:
			case SVC_SNMPv3:
			case SVC_AGENT:
				$external_param = ' "'.$key_.'"';
				break;
		}
		$result = discovery_check_type2str($type);
		if((svc_default_port($type) != $port) || ($type == SVC_TCP))
			$result .= ' ('.$port.')';
		$result .= $external_param;

		return $result;
	}

	function discovery_port2str($type_int, $port){
		$port_def = svc_default_port($type_int);

		if($port != $port_def){
			return ' ('.$port.')';
		}

	return '';
	}

	function discovery_status2str($status_int){
		switch($status_int){
			case DRULE_STATUS_ACTIVE:	$status = S_ACTIVE;		break;
			case DRULE_STATUS_DISABLED:	$status = S_DISABLED;	break;
			default:					$status = S_UNKNOWN;	break;
		}
		return $status;
	}

	function discovery_status2style($status){
		switch($status){
			case DRULE_STATUS_ACTIVE: $status = 'off'; break;
			case DRULE_STATUS_DISABLED: $status = 'on'; break;
			default: $status = 'unknown'; break;
		}
		return $status;
	}

	function discovery_object_status2str($status=null){
		$statuses = array(
			DOBJECT_STATUS_UP => S_UP,
			DOBJECT_STATUS_DOWN => S_DOWN,
			DOBJECT_STATUS_DISCOVER => S_DISCOVERED,
			DOBJECT_STATUS_LOST => S_LOST,
		);

		if(is_null($status)){
			order_result($statuses);
			return $statuses;
		}
		else if(isset($statuses[$status]))
			return $statuses[$status];
		else
			return S_UNKNOWN;
	}

	function get_discovery_rule_by_druleid($druleid){
		return DBfetch(DBselect('select * from drules where druleid='.$druleid));
	}

	function add_discovery_check($druleid, $type, $ports, $key, $snmp_community,
			$snmpv3_securityname, $snmpv3_securitylevel, $snmpv3_authpassphrase, $snmpv3_privpassphrase, $uniq=0)
	{
		// no need to store those items in DB if they will not be used
		if($snmpv3_securitylevel == ITEM_SNMPV3_SECURITYLEVEL_NOAUTHNOPRIV){
			$snmpv3_authpassphrase = '';
			$snmpv3_privpassphrase = '';
		}
		if($snmpv3_securitylevel == ITEM_SNMPV3_SECURITYLEVEL_AUTHNOPRIV){
			$snmpv3_privpassphrase = '';
		}

		$dcheckid = get_dbid('dchecks', 'dcheckid');
		$result = DBexecute('insert into dchecks (dcheckid,druleid,type,ports,key_,snmp_community'.
				',snmpv3_securityname,snmpv3_securitylevel,snmpv3_authpassphrase,snmpv3_privpassphrase,uniq) '.
				' values ('.$dcheckid.','.$druleid.','.$type.','.zbx_dbstr($ports).','.
				zbx_dbstr($key).','.zbx_dbstr($snmp_community).','.zbx_dbstr($snmpv3_securityname).','.
				$snmpv3_securitylevel.','.zbx_dbstr($snmpv3_authpassphrase).','.zbx_dbstr($snmpv3_privpassphrase).','.$uniq.')');

		if(!$result)
			return $result;

		return $dcheckid;
	}

	function add_discovery_rule($proxy_hostid, $name, $iprange, $delay, $status, $dchecks, $uniqueness_criteria){
		if( !validate_ip_range($iprange) ){
			error(S_INCORRECT_IP_RANGE);
			return false;
		}

		$druleid = get_dbid('drules', 'druleid');
		$result = DBexecute('insert into drules (druleid,proxy_hostid,name,iprange,delay,status) '.
			' values ('.$druleid.','.zero2null($proxy_hostid).','.zbx_dbstr($name).','.zbx_dbstr($iprange).','.$delay.','.$status.')');

		if($result && isset($dchecks)){
			foreach($dchecks as $id => $data){
				$data['dcheckid'] = add_discovery_check($druleid, $data['type'], $data['ports'], $data['key'],
						$data['snmp_community'], $data['snmpv3_securityname'], $data['snmpv3_securitylevel'],
						$data['snmpv3_authpassphrase'], $data['snmpv3_privpassphrase'],
						($uniqueness_criteria == $id) ? 1 : 0);
			}
			$result = $druleid;
		}

		return $result;
	}

	function update_discovery_rule($druleid, $proxy_hostid, $name, $iprange, $delay, $status, $dchecks,	$uniqueness_criteria, $dchecks_deleted){
		if(!validate_ip_range($iprange)){
			error(S_INCORRECT_IP_RANGE);
			return false;
		}

		$result = DBexecute('update drules set proxy_hostid='.zero2null($proxy_hostid).',name='.zbx_dbstr($name).',iprange='.zbx_dbstr($iprange).','.
			'delay='.$delay.',status='.$status.' where druleid='.$druleid);

		if($result && isset($dchecks)){
			$unique_dcheckid = 0;
			foreach($dchecks as $id => $data){
				if(!isset($data['dcheckid'])){
					$data['dcheckid'] = add_discovery_check($druleid, $data['type'], $data['ports'], $data['key'],
							$data['snmp_community'], $data['snmpv3_securityname'], $data['snmpv3_securitylevel'],
							$data['snmpv3_authpassphrase'], $data['snmpv3_privpassphrase']);
				}
				if($uniqueness_criteria == $id && $data['dcheckid'])
					$unique_dcheckid = $data['dcheckid'];
			}

			$sql = 'UPDATE dchecks SET uniq=0 WHERE druleid='.$druleid;
			DBexecute($sql);

			if($unique_dcheckid){
				$sql = 'UPDATE dchecks SET uniq=1 WHERE dcheckid='.$unique_dcheckid;
				DBexecute($sql);
			}
		}

		if($result && isset($dchecks_deleted) && !empty($dchecks_deleted))
			delete_discovery_check($dchecks_deleted);

	return $result;
	}

	function delete_discovery_check($dcheckids){
		$actionids = array();
// conditions
		$sql = 'SELECT DISTINCT actionid '.
				' FROM conditions '.
				' WHERE conditiontype='.CONDITION_TYPE_DCHECK.
					' AND '.DBcondition('value', $dcheckids);

		$db_actions = DBselect($sql);
		while($db_action = DBfetch($db_actions))
			$actionids[] = $db_action['actionid'];

// disabling actions with deleted conditions
		if (!empty($actionids)){
			DBexecute('UPDATE actions '.
					' SET status='.ACTION_STATUS_DISABLED.
					' WHERE '.DBcondition('actionid', $actionids));

// delete action conditions
			DBexecute('DELETE FROM conditions '.
					' WHERE conditiontype='.CONDITION_TYPE_DCHECK.
					' AND '.DBcondition('value', $dcheckids));
		}

		DBexecute('DELETE FROM dchecks WHERE '.DBcondition('dcheckid', $dcheckids));
	}

	function delete_discovery_rule($druleid){
		$actionids = array();
// conditions
		$sql = 'SELECT DISTINCT actionid '.
				' FROM conditions '.
				' WHERE conditiontype='.CONDITION_TYPE_DRULE.
					" AND value='$druleid'";

		$db_actions = DBselect($sql);
		while($db_action = DBfetch($db_actions))
			$actionids[] = $db_action['actionid'];

// disabling actions with deleted conditions
		if(!empty($actionids)){
			DBexecute('UPDATE actions '.
					' SET status='.ACTION_STATUS_DISABLED.
					' WHERE '.DBcondition('actionid', $actionids));

// delete action conditions
			DBexecute('DELETE FROM conditions '.
					' WHERE conditiontype='.CONDITION_TYPE_DRULE.
					" AND value='$druleid'");
		}

		$result = DBexecute('delete from drules where druleid='.$druleid);

		return $result;
	}
?>
