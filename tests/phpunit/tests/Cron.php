<?php

namespace Required\Digest\Tests;

use MockAction;
use Required\Digest\Queue;
use \WP_UnitTestCase;
use Required\Digest\Cron as Digest_Cron;

class Cron extends WP_UnitTestCase {
	protected $email_subject = '';

	public function filter_digest_cron_email_subject( $subject ) {
		$this->email_subject = $subject;

		Queue::clear();

		return $subject;
	}

	public function test_run_cron() {
		update_option( 'digest_frequency', array(
			'period' => 'daily',
			'hour'   => date( 'H' ),
			'day'    => date( 'w' ),
		) );

		Queue::add( 'foo@example.com', 'foo', 'bar' );

		add_filter( 'digest_cron_email_subject', array( $this, 'filter_digest_cron_email_subject' ) );
		Digest_Cron::init();
		remove_filter( 'digest_cron_email_subject', array( $this, 'filter_digest_cron_email_subject' ) );

		$this->assertSame(
			sprintf( __( 'Today on %s', 'digest' ), get_bloginfo( 'name' ) ),
			$this->email_subject
		);
	}

	public function test_run_cron_weekly() {
		update_option( 'digest_frequency', array(
			'period' => 'weekly',
			'hour'   => date( 'H' ),
			'day'    => date( 'w' ),
		) );

		Queue::add( 'foo@example.com', 'foo', 'bar' );

		add_filter( 'digest_cron_email_subject', array( $this, 'filter_digest_cron_email_subject' ) );
		Digest_Cron::init();
		remove_filter( 'digest_cron_email_subject', array( $this, 'filter_digest_cron_email_subject' ) );

		$this->assertSame(
			sprintf( __( 'Past Week on %s', 'digest' ), get_bloginfo( 'name' ) ),
			$this->email_subject
		);
	}

	public function test_run_cron_empty_queue() {
		update_option( 'digest_frequency', array(
			'period' => 'daily',
			'hour'   => date( 'H' ),
			'day'    => date( 'w' ),
		) );

		$action = new MockAction();

		add_filter( 'digest_cron_email_subject', array( $action, 'filter' ) );
		Digest_Cron::init();
		remove_filter( 'digest_cron_email_subject', array( $action, 'filter' ) );

		$this->assertSame( 0, $action->get_call_count() );
	}

	public function test_run_cron_wrong_hour() {
		update_option( 'digest_frequency', array(
			'period' => 'daily',
			'hour'   => date( 'H' ) + 1,
			'day'    => date( 'w' ),
		) );

		$action = new MockAction();

		add_filter( 'digest_cron_email_subject', array( $action, 'filter' ) );
		Digest_Cron::init();
		remove_filter( 'digest_cron_email_subject', array( $action, 'filter' ) );

		$this->assertSame( 0, $action->get_call_count() );
	}

	public function test_run_cron_wrong_day() {
		update_option( 'digest_frequency', array(
			'period' => 'daily',
			'hour'   => date( 'H' ),
			'day'    => date( 'w' ) + 1,
		) );

		$action = new MockAction();

		add_filter( 'digest_cron_email_subject', array( $action, 'filter' ) );
		Digest_Cron::init();
		remove_filter( 'digest_cron_email_subject', array( $action, 'filter' ) );

		$this->assertSame( 0, $action->get_call_count() );
	}
}
