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

class RenewRegistration extends Mailable {
	use Queueable, SerializesModels;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public $vehicle;
	public $reg_date;
	public $user;
	public function __construct($vehicle, $reg_date, $user) {
		$this->vehicle = $vehicle;
		$this->reg_date = $reg_date;
		$this->user = $user;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build() {
		return $this->from(AtlasLogistics::get("email"))->subject('Renew Vehicle Registration')->markdown('emails.renew_registration');
	}
}
