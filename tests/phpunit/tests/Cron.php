<?php

namespace Required\Digest\Tests;

use MockAction;
use Required\Digest\Queue;
use \WP_UnitTestCase;
use Required\Digest\Cron as Digest_Cron;

class Cron extends WP_UnitTestCase {
	protected $email_subject = '';

	public function setUp() {
		parent::setUp();

		delete_option( 'digest_frequency', false );
	}

	public function tearDown() {
		delete_option( 'digest_frequency', false );

		parent::tearDown();
	}

	public function filter_digest_cron_email_subject( $subject ) {
		$this->email_subject = $subject;

		Queue::clear();

		return $subject;
	}

	public function test_run_cron() {
		update_option( 'digest_frequency', [
			'period' => 'daily',
			'hour'   => date( 'G' ),
			'day'    => date( 'w' ),
		] );

		Queue::add( 'foo@example.com', 'foo', 'bar' );

		add_filter( 'digest_cron_email_subject', [ $this, 'filter_digest_cron_email_subject' ] );
		Digest_Cron::init();
		remove_filter( 'digest_cron_email_subject', [ $this, 'filter_digest_cron_email_subject' ] );

		$this->assertSame(
			sprintf( __( 'Today on %s', 'digest' ), get_bloginfo( 'name' ) ),
			$this->email_subject
		);
	}

	public function test_run_cron_weekly() {
		update_option( 'digest_frequency', [
			'period' => 'weekly',
			'hour'   => date( 'G' ),
			'day'    => date( 'w' ),
		] );

		Queue::add( 'foo@example.com', 'foo', 'bar' );

		add_filter( 'digest_cron_email_subject', [ $this, 'filter_digest_cron_email_subject' ] );
		Digest_Cron::init();
		remove_filter( 'digest_cron_email_subject', [ $this, 'filter_digest_cron_email_subject' ] );

		$this->assertSame(
			sprintf( __( 'Past Week on %s', 'digest' ), get_bloginfo( 'name' ) ),
			$this->email_subject
		);
	}

	public function test_run_cron_empty_queue() {
		update_option( 'digest_frequency', [
			'period' => 'daily',
			'hour'   => date( 'G' ),
			'day'    => date( 'w' ),
		] );

		$action = new MockAction();

		add_filter( 'digest_cron_email_subject', [ $action, 'filter' ] );
		Digest_Cron::init();
		remove_filter( 'digest_cron_email_subject', [ $action, 'filter' ] );

		$this->assertSame( 0, $action->get_call_count() );
	}

	public function test_run_cron_wrong_hour() {
		update_option( 'digest_frequency', [
			'period' => 'daily',
			'hour'   => date( 'H' ) + 1,
			'day'    => date( 'w' ),
		] );

		$action = new MockAction();

		add_filter( 'digest_cron_email_subject', [ $action, 'filter' ] );
		Digest_Cron::init();
		remove_filter( 'digest_cron_email_subject', [ $action, 'filter' ] );

		$this->assertSame( 0, $action->get_call_count() );
	}

	public function test_run_cron_wrong_day() {
		update_option( 'digest_frequency', [
			'period' => 'weekly',
			'hour'   => date( 'H' ),
			'day'    => date( 'w' ) + 1,
		] );

		$action = new MockAction();

		add_filter( 'digest_cron_email_subject', [ $action, 'filter' ] );
		Digest_Cron::init();
		remove_filter( 'digest_cron_email_subject', [ $action, 'filter' ] );

		$this->assertSame( 0, $action->get_call_count() );
	}
}
