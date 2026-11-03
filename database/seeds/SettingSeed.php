<?php

use App\Model\ApiSettings;
use App\Model\EmailContent;
use App\Model\Settings;
use Illuminate\Database\Seeder;

class SettingSeed extends Seeder
{

    public function run()
    {
        $this->default_settings();
        $this->api_settings();
        $this->email_settings();
    }

    private function default_settings()
    {
        DB::table('settings')->truncate();

        Settings::create(['label' => 'Website Name', 'name' => 'app_name', 'value' => 'Atlas Logistics']);

        Settings::create(['label' => 'Business Address 1', 'name' => 'badd1', 'value' => 'Company Address 1']);
        Settings::create(['label' => 'Business Address 2', 'name' => 'badd2', 'value' => 'Company Address 2']);
        Settings::create(['label' => 'Email Address', 'name' => 'email', 'value' => 'master@admin.com']);
        Settings::create(['label' => 'City', 'name' => 'city', 'value' => 'Bhavnagar']);
        Settings::create(['label' => 'State', 'name' => 'state', 'value' => 'Gujarat']);
        Settings::create(['label' => 'Country', 'name' => 'country', 'value' => 'India']);
        Settings::create(['label' => 'Distence Format', 'name' => 'dis_format', 'value' => 'km']);
        Settings::create(['label' => 'Language', 'name' => 'language', 'value' => 'English-en']);
        Settings::create(['label' => 'Currency', 'name' => 'currency', 'value' => '₹']);
        Settings::create(['label' => 'Tax No', 'name' => 'tax_no', 'value' => 'ABCD8735XXX']);
        Settings::create(['label' => 'Invoice Text', 'name' => 'invoice_text', 'value' => 'Etsy doostang zoodles disqus groupon greplin oooj voxy zoodles, weebly ning heekya handango imeem plugg dopplr jibjab, movity jajah plickers sifteo edmodo ifttt zimbra.']);
        Settings::create(['label' => 'Small Logo', 'name' => 'icon_img', 'value' => 'logo-40.png']);
        Settings::create(['label' => 'Main Logo', 'name' => 'logo_img', 'value' => 'logo.png']);
        Settings::create(['label' => 'Time Interval', 'name' => 'time_interval', 'value' => 30]);
        Settings::create(['label' => 'Tax Charge', 'name' => 'tax_charge', 'value' => 'null']);
        Settings::create(['label' => 'Fuel Unit', 'name' => 'fuel_unit', 'value' => 'gallon']);
        Settings::create(['label' => 'Date Format', 'name' => 'date_format', 'value' => 'd-m-Y']);
        Settings::create(['label' => 'Fuel enable for Driver', 'name' => 'fuel_enable_driver', 'value' => 0]);
        Settings::create(['label' => 'Income enable for Driver', 'name' => 'income_enable_driver', 'value' => 0]);
		Settings::create(['label' => 'Expense enable for Driver', 'name' => 'expense_enable_driver', 'value' => 0]);
    }

    private function api_settings()
    {
        DB::table('api_settings')->truncate();
        ApiSettings::create(['key_name' => 'api', 'key_value' => '1']);
        ApiSettings::create(['key_name' => 'anyone_register', 'key_value' => '0']);
        ApiSettings::create(['key_name' => 'api_key', 'key_value' => '']);
        ApiSettings::create(['key_name' => 'db_url', 'key_value' => '']);
        ApiSettings::create(['key_name' => 'db_secret', 'key_value' => '']);
        ApiSettings::create(['key_name' => 'server_key', 'key_value' => '']);
    }


    private function email_settings()
    {
        DB::table('email_content')->truncate();
        EmailContent::create(['key' => 'insurance', 'value' => 'vehicle insurance email content']);
        EmailContent::create(['key' => 'vehicle_licence', 'value' => 'vehicle licence email content']);
        EmailContent::create(['key' => 'driving_licence', 'value' => 'driving licence email content']);
        EmailContent::create(['key' => 'registration', 'value' => 'vehicle registration email content']);
        EmailContent::create(['key' => 'service_reminder', 'value' => 'service reminder email content']);
        EmailContent::create(['key' => 'users', 'value' => '']);
        EmailContent::create(['key' => 'options', 'value' => '']);
        EmailContent::create(['key' => 'email', 'value' => 0]);

    }




}
