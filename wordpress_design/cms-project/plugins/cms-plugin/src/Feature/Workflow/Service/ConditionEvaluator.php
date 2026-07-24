<?php

namespace Ah\Cms\Feature\Workflow\Service;

defined( 'ABSPATH' ) || exit;

/**
 * Condition Evaluator — evaluates rule conditions against context data.
 * Supports legacy flat format and grouped condition format with all/any matching.
 */
class ConditionEvaluator {

	/**
	 * Check if a rule's conditions pass against the given context.
	 */
	public static function evaluate( object $rule, array $context ): bool {
		if ( empty( $rule->conditions ) ) {
			return true;
		}

		$conditions = $rule->conditions;
		$anyTop     = ( 'any' === $rule->conditions_match );

		// Legacy flat format: first element has a 'field' key
		if ( isset( $conditions[0]['field'] ) ) {
			return self::evaluateFlat( $conditions, $context, $anyTop );
		}

		// Group format: each element has 'match' + 'conditions'
		foreach ( $conditions as $group ) {
			$anyGroup  = ( 'any' === ( $group['match'] ?? 'all' ) );
			$groupPass = self::evaluateFlat( $group['conditions'] ?? array(), $context, $anyGroup );
			if ( $anyTop && $groupPass ) {
				return true;
			}
			if ( ! $anyTop && ! $groupPass ) {
				return false;
			}
		}

		return ! $anyTop;
	}

	/**
	 * Evaluate a flat list of conditions.
	 */
	private static function evaluateFlat( array $conditions, array $context, bool $any ): bool {
		if ( empty( $conditions ) ) {
			return true;
		}

		foreach ( $conditions as $c ) {
			$actual   = strtolower( trim( (string) ( $context[ $c['field'] ] ?? '' ) ) );
			$expected = strtolower( trim( $c['value'] ?? '' ) );
			$op       = $c['operator'] ?? 'equals';
			$list     = array_map( 'trim', explode( ',', $expected ) );

			$pass = match ( $op ) {
				'equals'       => $actual === $expected,
				'not_equals'   => $actual !== $expected,
				'contains'     => str_contains( $actual, $expected ),
				'not_contains' => ! str_contains( $actual, $expected ),
				'starts_with'  => str_starts_with( $actual, $expected ),
				'ends_with'    => str_ends_with( $actual, $expected ),
				'is_empty'     => $actual === '',
				'is_not_empty' => $actual !== '',
				'greater_than' => is_numeric( $actual ) && is_numeric( $expected ) && ( (float) $actual > (float) $expected ),
				'less_than'    => is_numeric( $actual ) && is_numeric( $expected ) && ( (float) $actual < (float) $expected ),
				'in_list'      => in_array( $actual, $list, true ),
				'not_in_list'  => ! in_array( $actual, $list, true ),
				default        => false,
			};

			if ( $any && $pass ) {
				return true;
			}
			if ( ! $any && ! $pass ) {
				return false;
			}
		}

		return ! $any;
	}
}
