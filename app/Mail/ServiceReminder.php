<?php
/*
@copyright

Fleet Manager v7.0

Copyright (C) 2017-2023 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>

 */
namespace App\Mail;

use App\Model\AtlasLogistics;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ServiceReminder extends Mailable {
	use Queueable, SerializesModels;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public $detail;
	public $vehicle;
	public $date;
	public $diff_in_days;
	public $user;

	public function __construct($detail, $vehicle, $date, $diff_in_days, $user) {
		$this->detail = $detail;
		$this->vehicle = $vehicle;
		$this->date = $date;
		$this->diff_in_days = $diff_in_days;
		$this->user = $user;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build() {
		return $this->from(AtlasLogistics::get("email"))->subject('Service Reminder')->markdown('emails.service_reminder');
	}
}
