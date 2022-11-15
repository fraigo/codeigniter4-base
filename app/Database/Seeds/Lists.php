<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class Lists extends Seeder
{
    public function run()
    {
        $this->db->table('lists')->truncate();

        $data = [
        ];
        $data["Country"] = [
            "us" => "USA",
            "ca" => "Canada"
        ];
        $data["Timezone"] = [
            "America/Vancouver" => "",
            "America/New_York" => "",
            "America/Santiago" => ""
        ];
        $data["Language"] = [
            "en" => "English",
            "es" => "Español",
            "fr" => "Français"
        ];
        foreach($data as $name => $items){
            foreach($items as $value => $label){
                if (empty($label)){
                    $label = $value;
                }
                $data = [
                    "name" => $name,
                    "value" => $value,
                    "label" => $label,
                ];
                $this->db->table('lists')->insert($data);    
            }
        }
    }
}
