<?php declare(strict_types = 1);
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


/**
 * Class for validating history functions.
 */
class CHistFunctionValidator extends CValidator {

	/**
	 * An options array.
	 *
	 * Supported options:
	 *   'parameters' => []      Definition of parameters of known history functions.
	 *   'calculated' => false   Validate history function as part of calculated item formula.
	 *   'aggregating' => false  Validate as aggregating history function.
	 *
	 * @var array
	 */
	private $options = [
		'parameters' => [],
		'calculated' => false,
		'aggregating' => false
	];

	/**
	 * @param array $options
	 */
	public function __construct(array $options = []) {
		$this->options = $options + $this->options;
	}

	/**
	 * Validate history function.
	 *
	 * @param array $token  A token of CExpressionParserResult::TOKEN_TYPE_HIST_FUNCTION type.
	 *
	 * @return bool
	 */
	public function validate($token) {
		// TODO: remove the temporary stub
		return true;

		$invalid_param_messages = [
			_('invalid first parameter in function "%1$s"'),
			_('invalid second parameter in function "%1$s"'),
			_('invalid third parameter in function "%1$s"'),
			_('invalid fourth parameter in function "%1$s"'),
			_('invalid fifth parameter in function "%1$s"')
		];

		if (!array_key_exists($token['data']['function'], $this->options['parameters'])) {
			$this->setError(_s('unknown function "%1$s"', $token['data']['function']));

			return false;
		}

		$params = $token['data']['parameters'];
		$params_spec = $this->options['parameters'][$token['data']['function']];

		if (count($params) > count($params_spec)) {
			$this->setError(_s('invalid number of parameters in function "%1$s"', $token['data']['function']));

			return false;
		}

		foreach ($params_spec as $index => $param_spec) {
			$required = !array_key_exists('required', $param_spec) || $param_spec['required'];

			if ($index >= count($params)) {
				if ($required) {
					$this->setError(
						_s('mandatory parameter is missing in function "%1$s"', $token['data']['function'])
					);

					return false;
				}

				continue;
			}

			$param = $params[$index];

			if ($param['match'] === '') {
				if ($required) {
					$this->setError(_params($invalid_param_messages[$index], [$token['data']['function']]));

					return false;
				}

				continue;
			}

			switch ($param['type']) {
				case CHistFunctionParser::PARAM_TYPE_PERIOD:
					if (self::hasMacros($param['data']['sec_num']) && $param['data']['time_shift'] === '') {
						continue 2;
					}
					break;

				case CHistFunctionParser::PARAM_TYPE_QUOTED:
					if (self::hasMacros(CHistFunctionParser::unquoteParam($param['match']))) {
						continue 2;
					}
					break;

				case CHistFunctionParser::PARAM_TYPE_UNQUOTED:
					if (self::hasMacros($param['match'])) {
						continue 2;
					}
					break;
			}

			if (array_key_exists('rules', $param_spec)) {
				$is_valid = self::validateRules($param, $param_spec['rules'], $this->options);

				if (!$is_valid) {
					$this->setError(_params($invalid_param_messages[$index], [$token['data']['function']]));

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Loose check if string value contains macros.
	 *
	 * @param string $value
	 *
	 * @static
	 *
	 * @return bool
	 */
	private static function hasMacros(string $value): bool {
		return (strpos($value, '{') !== false);
	}

	/**
	 * Validate function parameter token's compliance to the rules.
	 *
	 * @param array $param    Function parameter token.
	 * @param array $rules
	 * @param array $options
	 *
	 * @static
	 *
	 * @return bool
	 */
	private static function validateRules(array $param, array $rules, array $options): bool {
		$param_match_unquoted = ($param['type'] == CHistFunctionParser::PARAM_TYPE_QUOTED)
			? CHistFunctionParser::unquoteParam($param['match'])
			: $param['match'];

		foreach ($rules as $rule) {
			switch ($rule['type']) {
				case 'query':
					if ($param['type'] != CHistFunctionParser::PARAM_TYPE_QUERY) {
						return false;
					}

					if (!self::validateQuery($param['data']['host'], $param['data']['item'], $param['data']['filter'],
							$options)) {
						return false;
					}

					break;

				case 'period':
					if ($param['type'] != CHistFunctionParser::PARAM_TYPE_PERIOD) {
						return false;
					}

					if (!self::validatePeriod($param['data']['sec_num'], $param['data']['time_shift'], $rule['mode'])) {
						return false;
					}

					break;

				case 'number':
					$with_suffix = array_key_exists('with_suffix', $rule) && $rule['with_suffix'];

					$parser = new CNumberParser(['with_minus' => true, 'with_suffix' => $with_suffix]);

					if ($parser->parse($param_match_unquoted) != CParser::PARSE_SUCCESS) {
						return false;
					}

					$value = $parser->calcValue();

					if ((array_key_exists('min', $rule) && $value < $rule['min'])
							|| array_key_exists('max', $rule) && $value > $rule['max']) {
						return false;
					}

					break;

				case 'regexp':
					if (preg_match($rule['pattern'], $param_match_unquoted) != 1) {
						return false;
					}

					break;

				case 'time':
					$with_year = array_key_exists('with_year', $rule) && $rule['with_year'];
					$min = array_key_exists('min', $rule) ? $rule['min'] : ZBX_MIN_INT32;
					$max = array_key_exists('max', $rule) ? $rule['max'] : ZBX_MAX_INT32;

					$sec = timeUnitToSeconds($param_match_unquoted, $with_year);

					if ($sec === null || $sec < $min || $sec > $max) {
						return false;
					}

					break;

				default:
					return false;
			}
		}

		return true;
	}

	/**
	 * Validate function's query parameter.
	 *
	 * @param string $host
	 * @param string $item
	 * @param array  $filter   Filter token.
	 * @param array  $options
	 *
	 * @static
	 *
	 * @return bool
	 */
	private static function validateQuery(string $host, string $item, array $filter, array $options): bool {
		if ($options['calculated']) {
			if ($options['aggregating']) {
				if ($host === CQueryParser::HOST_ITEMKEY_WILDCARD && $item === CQueryParser::HOST_ITEMKEY_WILDCARD) {
					return false;
				}
			}
			else {
				if ($filter['match'] !== '') {
					return false;
				}

				if ($host === CQueryParser::HOST_ITEMKEY_WILDCARD || $item === CQueryParser::HOST_ITEMKEY_WILDCARD) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Validate function's period parameter.
	 *
	 * @param string $sec_num
	 * @param string $time_shift
	 * @param int    $mode
	 *
	 * @static
	 *
	 * @return bool
	 */
	private static function validatePeriod(string $sec_num, string $time_shift, int $mode): bool {
		switch ($mode) {
			case CHistFunctionData::PERIOD_MODE_DEFAULT:
				if ($sec_num === '' || self::hasMacros($sec_num)) {
					return true;
				}

				$sec = timeUnitToSeconds($sec_num);

				if ($sec !== null) {
					return ($sec > 0 && $sec <= ZBX_MAX_INT32);
				}

				if (preg_match('/^#(?<num>\d+)$/', $sec_num, $matches) == 1) {
					return ($matches['num'] > 0 && $matches['num'] <= ZBX_MAX_INT32);
				}

				return false;

			case CHistFunctionData::PERIOD_MODE_SEC:
				if ($time_shift !== '') {
					return false;
				}

				$sec = timeUnitToSeconds($sec_num);

				if ($sec !== null) {
					return ($sec > 0 && $sec <= ZBX_MAX_INT32);
				}

				return false;

			case CHistFunctionData::PERIOD_MODE_NUM:
				if (preg_match('/^#(?<num>\d+)$/', $sec_num, $matches) == 1) {
					return ($matches['num'] > 0 && $matches['num'] <= ZBX_MAX_INT32);
				}

				return false;

			case CHistFunctionData::PERIOD_MODE_TREND:
				if ($time_shift === '') {
					return false;
				}

				if (self::hasMacros($sec_num)) {
					return true;
				}

				$sec = timeUnitToSeconds($sec_num, true);

				if ($sec !== null) {
					return ($sec > 0 && $sec <= ZBX_MAX_INT32 && $sec % SEC_PER_HOUR == 0);
				}

				return false;

			default:
				return false;
		}

		return false;
	}
}
