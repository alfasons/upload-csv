<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ContactController extends Controller
{
    public function showImportForm()
    {
        // Get database columns for the 'contacts' table
        //$dbColumns = Schema::getColumnListing('contacts');
        $dbColumns = ['first_name', 'last_name', 'email','number','address','extra','city','state','zip','owner_first_name','owner_last_name']; // Replace 'column1', 'column2', 'column3' with your actual column names
        $contacts = ContactList::all(); // Assuming you have a Contact model
        $tags = [['id'=>1,'name'=>'today']]; //Tag::all(); // Assuming you have a Tag model

        return view('contacts.import', compact('dbColumns','contacts','tags'));
    }

    public function uploadCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $path = $request->file('csv_file')->store('temp');
        $fullPath = storage_path('app/' . $path);

        // Read CSV headers and first 5 rows
        $file = fopen($fullPath, 'r');
        $headers = fgetcsv($file);
        $sampleData = [];
        for ($i = 0; $i < 5 && !feof($file); $i++) {
            $sampleData[] = fgetcsv($file);
        }
        fclose($file);

        return response()->json([
            'headers' => $headers,
            'sampleData' => $sampleData,
            'filePath' => $path,
        ]);
    }

    public function processMapping(Request $request)
    {
        $mapping = $request->mapping;
        $filePath = storage_path('app/' . $request->filePath);

        $file = fopen($filePath, 'r');
        $headers = fgetcsv($file);

        while (($row = fgetcsv($file)) !== FALSE) {
            $rowData = [];
            foreach ($headers as $key => $header) {
                if (!empty($mapping[$header])) {
                    $rowData[$mapping[$header]] = $row[$key];
                }
            }
            Contact::create($rowData);
        }
        fclose($file);

        Storage::delete($request->filePath);

        return redirect()->route('contacts.import')->with('success', 'Contacts imported successfully with mapping.');
    }
    public function completeImport(Request $request)
    {
        Log::info('uuuuu');
        Log::info('Import Request:', $request->all());
       // echo print_r($request,true);
        $mapping = $request->mapping;
        $filePath = storage_path('app/' . $request->filePath);

        $file = fopen($filePath, 'r');
        $headers = fgetcsv($file);

        while (($row = fgetcsv($file)) !== FALSE) {
            $rowData = [];
            foreach ($headers as $key => $header) {
                if (!empty($mapping[$header])) {
                    $rowData[$mapping[$header]] = $row[$key];
                }
            }
            Log::info('Import Request:', $rowData);
            Contact::create($rowData);
        }
        fclose($file);

        Storage::delete($request->filePath);

        return redirect()->route('contacts.import')->with('success', 'Contacts imported successfully with mapping.');
    }
}
