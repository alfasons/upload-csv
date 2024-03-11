<?php
namespace App\Imports;

use App\Models\Contact;
use Maatwebsite\Excel\Concerns\ToModel;

class ContactsImport implements ToModel
{
    public function model(array $row)
    {
        return new Contact([
            'first_name'     => $row[0],
            'last_name'    => $row[1],
            'email'    => $row[2],
            // Map other fields...
        ]);
    }
}
