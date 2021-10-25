<?php

namespace Tests\Unit;

use App\Services\CommissionService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommissionTest extends TestCase
{
    /**
     * testing calculate deposit operation type.
     *
     * @return void
     */

     private $currencies = [
        "EUR"           => 1,
        "USD"           => 1.1497,
        "JPY"           => 129.53,
     ];

    public function test_calculate_deposit_operation_type()
    {
        $this->withExceptionHandling();

        $com_model  = new CommissionService();
        $data       = array(
            [
                "op_type"   => "deposit",
                "user_id"   => "1",
                "user_type" => "private",
                "amount"    => "200.00",
                "date"      => "2020-12-15",
                "currency"  => "EUR"
            ],
            [
                "op_type"   => "deposit",
                "user_id"   => "1",
                "user_type" => "private",
                "amount"    => "10000.00",
                "date"      => "2020-12-14",
                "currency"  => "EUR"
            ]
        );
        $com_model->set_currencies($this->currencies);
        $result = $com_model->process($data);
        $this->assertEquals($result,[0.06,3.0]);

    }
    /**
     * testing calculate withdraw operation type (private,business).
     *
     * @return void
     */
    public function test_calculate_withdraw_operation()
    {
        $this->withExceptionHandling();

        $com_model  = new CommissionService();
        $data       = array(
            [
                "op_type"   => "withdraw",
                "user_id"   => "1",
                "user_type" => "private",
                "amount"    => "120000.00",
                "date"      => "2020-12-15",
                "currency"  => "JPY"
            ],
            [
                "op_type"   => "withdraw",
                "user_id"   => "1",
                "user_type" => "business",
                "amount"    => "500.00",
                "date"      => "2020-12-16",
                "currency"  => "USD"
            ],
            [
                "op_type"   => "withdraw",
                "user_id"   => "1",
                "user_type" => "private",
                "amount"    => "10000.00",
                "date"      => "2020-12-17",
                "currency"  => "EUR"
            ],
            [
                "op_type"   => "withdraw",
                "user_id"   => "1",
                "user_type" => "business",
                "amount"    => "1234.00",
                "date"      => "2020-12-14",
                "currency"  => "USD"
            ]
        );

        $com_model->set_currencies($this->currencies);
        $result = $com_model->process($data);
        $this->assertEquals($result,[0,2.5,29.78,6.17]);

    }
}
