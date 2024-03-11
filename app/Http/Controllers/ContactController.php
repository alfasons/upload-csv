<?php
namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ContactController extends Controller
{
    public function showImportForm()
    {
        // Get database columns for the 'contacts' table
        $dbColumns = Schema::getColumnListing('contacts');
        return view('contacts.import', compact('dbColumns'));
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
}
