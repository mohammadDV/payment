<?php

namespace App\Traits;

trait CSV {
    /**
     * Read CSV file.
     *
     * @return array of data
     */
    private function read_file(string $csvFile): array{
        $line_of_text = [];
        $file_handle = fopen($csvFile, 'r');
        while (!feof($file_handle)) {
            $line_of_text[] = fgetcsv($file_handle,0,",");
        }
        fclose($file_handle);
        $result = [];
        foreach ($line_of_text as $item){
            if (empty($item)) { continue; }
            if (is_array($item)){
                $temp =  $this->compile_row(trim($item[0]));
            }else{
                $temp =  $this->compile_row(trim($item));
            }
            if (!empty($temp)){
                $result[] = $temp;
            }
        }

        return $result;
    }
    /**
     * Inspect and check the data row.
     *
     * @return array of data row
     */
    private function compile_row(string $item): array {
        $result = [];
        $array  = explode(",",$item);
        if (!empty($array[5])){
            if(empty($array[2]) || !in_array($array[2],["private","business"])) {
                throw new \Exception($array[2] . ' is not in user type list.');
            }
            if(empty($array[3]) || !in_array($array[3],["deposit","withdraw"])) {
                throw new \Exception($array[3] . ' is not in operation type list.');
            }
            if(empty($array[5]) || !in_array($array[5],array_keys(Config("payment.currencies")))) {
                throw new \Exception($array[5] . ' is not in currency list.');
            }

            $result["date"]         = !empty($array[0]) ? trim($array[0]) : "";
            $result["user_id"]      = !empty($array[1]) ? trim($array[1]) : "";
            $result["user_type"]    = !empty($array[2]) ? trim($array[2]) : "";
            $result["op_type"]      = !empty($array[3]) ? trim($array[3]) : "";
            $result["amount"]       = !empty($array[4]) ? trim($array[4]) : "";
            $result["currency"]     = !empty($array[5]) ? trim($array[5]) : "";
        }
        return $result;
    }
}
