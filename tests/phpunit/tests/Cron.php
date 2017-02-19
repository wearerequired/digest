<?php

namespace Required\Digest\Tests;

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

	public function test_not_ready() {
		Digest_Cron::init();

		$this->assertEmpty( $this->email_subject );
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
}
