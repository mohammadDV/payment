<?php

declare (strict_types = 1);

namespace App\Services;

use App\Traits\CSV;

class CommissionService {
    /**
     * Commission service.
     *
     * @return array of commissions
     */
    use CSV;

    private $max_amount;
    private $max_count;
    private $prime_currency;
    private $d_fee;
    private $p_fee;
    private $b_fee;
    private $week_amount      = [];
    private $week_count       = [];
    private $currencies       = [];
    private $data             = [];
    /**
     * Setting data for the service.
     *
     * @return void
     */
    public function __construct()
    {
        $this->max_amount       = Config("payment.max_amount");
        $this->max_count        = Config("payment.max_count");
        $this->prime_currency   = Config("payment.prime_currency");
        $this->d_fee            = Config("payment.commission.deposit");
        $this->p_fee            = Config("payment.commission.withdraw.private");
        $this->b_fee            = Config("payment.commission.withdraw.business");
        $csvFile                = public_path(Config("payment.dir_file"));
        $this->data             = $this->read_file($csvFile);
    }
    /**
     * Processing data.
     *
     * @return array of commissions
     */
    public function process(array $data = []): array
    {
        if (!empty($data)){
            $this->data             = $data;
        }
        $result = [];
        foreach ($this->data as $item){
            $result[] = $this->calculate($item);
        }
        return $result;
    }
    /**
     * Calculating conditions on the data.
     *
     * @return float (amount) of commission
     */
    private function calculate(array $item): float
    {
        if ($item["op_type"] == "deposit") { // deposit operation type
            $amount                         = $this->convert(floatval($item["amount"]),$item["currency"]);
            $cmn                            = $amount * $this->d_fee;
        }else{
            if ($item["user_type"] == "private") { // withdraw operation type and private user type
                $amount                     = $this->convert(floatval($item["amount"]),$item["currency"]);
                $uniq                       = $item["user_id"] . "_" . date("oW", strtotime($item["date"]));
                $this->week_amount[$uniq]   = !empty($this->week_amount[$uniq]) ? $this->week_amount[$uniq] + $amount : $amount;
                $this->week_count[$uniq]    = !empty($this->week_count[$uniq]) ? $this->week_count[$uniq] + 1 : 1;

                var_dump($this->week_count[$uniq]);
                if ($this->week_amount[$uniq] <= $this->max_amount && $this->week_count[$uniq] <= $this->max_count){
                    $cmn = 0;
                }elseif($this->week_count[$uniq] <= $this->max_count){
                    $x                      = $this->week_amount[$uniq] - $amount;
                    $free_amount            = $this->max_amount > $x ? $this->max_amount - $x : 0;
                    $z                      = $amount - $free_amount;
                    $cmn                    = $z * $this->p_fee;
                }else{
                    $cmn = $amount * $this->p_fee;
                }
            }else{ // withdraw operation type and business user type
                $amount = $this->convert(floatval($item["amount"]),$item["currency"]);
                $cmn    = $amount * $this->b_fee;
            }
        }
        return rnd_float($this->convert(floatval($cmn),$item["currency"],2));
    }
    /**
     * Change and exchange currency.
     *
     * @return float (amount) of currency
     */
    private function convert(float $amount,string $currency, int $type = 1): float
    {
        if ($this->prime_currency !== strtoupper($currency)){
            if(empty($this->currencies[$currency])) {
                throw new \Exception($currency . ' is not in currency list.',1012);
            }
            if ($type == 1){
                $amount = $amount / $this->currencies[$currency];
            }else{
                $amount = $amount * $this->currencies[$currency];
            }
        }
        return $amount;
    }
    /**
     * Set exist currency.
     *
     * @return void fill the currencies property
     */
    public function set_currencies(array $data){
        $currencies = Config("payment.currencies");
        foreach ($currencies as $currency) {
            if (empty($data[$currency])){
                Throw new \Exception($currency . ' is not in API currency list.');
            }
            $this->currencies[$currency] = $data[$currency];
        }
    }
}
