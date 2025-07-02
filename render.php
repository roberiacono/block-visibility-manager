<?php
/**
 * Render callback for Block Visibility Manager.
 *
 * @package BlockVisibilityManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attributes = $block['attrs'] ?? array();

if ( empty( $attributes['bvmEnableVisibility'] ) ) {
	echo wp_kses_post( $block_content );
	return;
}

$visible = true;

// Time.
if ( ! empty( $attributes['bvmEnableTime'] ) && $attributes['bvmEnableTime'] ) {
	$now = current_time( 'H:i' );

	$from = $attributes['bvmTimeRange']['from'] ?? null;
	$to   = $attributes['bvmTimeRange']['to'] ?? null;

	if ( $from && $to ) {
		// Format times as strings "HH:MM".
		$time_from = sprintf( '%02d:%02d', $from['hours'], $from['minutes'] );
		$time_to   = sprintf( '%02d:%02d', $to['hours'], $to['minutes'] );

		if ( $now < $time_from || $now > $time_to ) {
			$visible = false;
		}
	}
}

// Date.
if ( ! empty( $attributes['bvmEnableDate'] ) && $attributes['bvmEnableDate'] ) {
	$today = new DateTime( current_time( 'Y-m-d' ) );

	$date_from_str = $attributes['bvmDateRange']['from'] ?? null;
	$date_to_str   = $attributes['bvmDateRange']['to'] ?? null;

	if ( $date_from_str && $date_to_str ) {
		// Remove milliseconds or invalid parts, or use only the date portion.
		$date_from_str = substr( $date_from_str, 0, 10 ); // "2025-07-12"
		$date_from     = new DateTime( $date_from_str );

		$date_to_str = substr( $date_to_str, 0, 10 ); // "2025-07-12"
		$date_to     = new DateTime( $date_to_str );

		if ( $date_from && $date_to ) {
			if ( $today < $date_from || $today > $date_to ) {
				$visible = false;
			}
		}
	}
}

if ( ! $visible ) {
	return '';
}


// Device: just CSS (handled via class).
$classes = '';
if ( ! empty( $attributes['bvmHideOnMobile'] ) ) {
	$classes .= ' hide-mobile';
}
if ( ! empty( $attributes['bvmHideOnTablet'] ) ) {
	$classes .= ' hide-tablet';
}
if ( ! empty( $attributes['bvmHideOnDesktop'] ) ) {
	$classes .= ' hide-desktop';
}


// User Roles.
if ( ! empty( $attributes['bvmUserRoles'] ) ) {
	if ( ! is_user_logged_in() ) {
		if ( in_array( 'guest', $attributes['bvmUserRoles'], true ) ) {
			return '';
		}
	}
	$user    = wp_get_current_user();
	$blocked = array_intersect( $attributes['bvmUserRoles'], $user->roles );
	if ( ! empty( $blocked ) ) {
		return;
	}
}

echo '<div class="' . esc_attr( trim( $classes ) ) . '">' . wp_kses_post( $block_content ) . '</div>';
