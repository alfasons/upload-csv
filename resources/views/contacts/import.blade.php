<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Import Contacts</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />


    <style>
        .drag-drop-area {
    border: 2px dashed #ccc;
    border-radius: 5px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
}

.drag-drop-area:hover {
    border-color: #aaa;
}

    </style>
</head>
<body>
<div class="container mt-5">
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#importModal">Import Contacts</button>

    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Contacts</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
              
                <div class="modal-body" style="max-height: calc(100vh - 210px); overflow-y: auto;"> <!-- Scrollable modal body -->
                    <!-- Step 1: Upload CSV -->
                    <div class="import-step" id="step1">
                        <form id="uploadForm">
                            <div class="form-group">
                                <label for="csv_file"> STEP 1 : Upload CSV file</label>
                                <div id="drop_area" class="drag-drop-area">
                                    <p id="drag_text">Drag and drop your CSV file here or click to select a file.</p>
                                    <input type="file" class="form-control-file" id="csv_file" name="csv_file" required hidden>
                                    <p id="file_name"></p> <!-- Element to display the file name -->
                                </div>
                               
                            </div>
                            <button type="submit" class="btn btn-info btn-sm">Upload</button>
                        </form>
                        
                    </div>
                    <!-- Step 2: Map Columns -->
                    <div class="import-step" id="step2" style="display: none;">
                        <h4> STEP 2 : Map Columns</h4>
                       
                            <form id="mappingForm">
                                <div class="table table-responsive">
                            <!--    <input type="hidden" name="filePath" id="filePath">-->
                                <table class="table table-sm table-striped text-sm" style="font-size:12px;white-space: nowrap;">
                                    <thead>
                                        <tr id="csvHeaders"></tr>
                                    </thead>
                                    <tbody id="csvPreview"></tbody>
                                </table>
                               
                          
                        </div>
                        <button type="button" id="backToStep1" class="btn btn-secondary btn-sm fa fa-reply">Go Back</button>

                        <button type="button" id="toStep3" class="btn  btn-sm btn-info">Next</button>

                    </form>
                    </div>
                    <!-- Step 3: Additional Fields -->
                    <div class="import-step" id="step3" style="display: none;">
                        <h4> STEP 3: Complete</h4>
                        <form id="detailsForm">
                            <input type="hidden" name="filePath" id="filePath">
                            <div class="form-group">
                                <label for="contactList">Select List</label>
                                <select id="contactList" name="contact_id" class="form-control">
                                    <option value="" disabled selected>Choose a contact</option>
                                    @foreach ($contacts as $contact)
                                        <option value="{{ $contact->id }}">{{ $contact->name }}</option> <!-- Adjust 'name' based on your Contact model -->
                                    @endforeach
                                </select>
                            </div>
                                                    <!-- Dropdown for Tags -->
                          <!-- Dropdown for Tags with multiple selection enabled -->
                                <div class="form-group">
                                    <label for="tagList">Tags</label>
                                    <select id="tagList" name="tag_ids[]" class="form-control" multiple>
                                        <option value="" disabled>Select tags</option>
                                        @foreach ($tags as $tag)
                                            <option value="{{ $tag['id'] }}">{{ $tag['name'] }}</option> <!-- Adjust based on your Tag model -->
                                        @endforeach
                                    </select>
                                </div>
                            <button type="button" id="backToStep2" class="btn btn-secondary btn-sm">Go Back</button>

                            <button type="submit" class="btn btn-success btn-sm">Complete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery, Popper.js, Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
$(document).ready(function() {
     // Back to Step 1 from Step 2
     $('#backToStep1').click(function() {
        $('#step2').hide();
        $('#step1').show();
    });

    // Back to Step 2 from Step 3
    $('#backToStep2').click(function() {
        $('#step3').hide();
        $('#step2').show();
    });
      // Set up AJAX headers with CSRF token
      $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var dropArea = document.getElementById('drop_area');
    var fileInput = document.getElementById('csv_file');
    var fileNameElement = document.getElementById('file_name');


function updateFileNameDisplay(files) {
    var dragText = document.getElementById('drag_text');

    if (files.length > 0) {
        fileNameElement.textContent = "Selected file: " + files[0].name;
        dragText.style.display = 'none'; // Hide the drag-and-drop instruction text
    } else {
        fileNameElement.textContent = "";
        dragText.style.display = ''; // Show the text again if there are no files
    }
}


// Update file name display on file input change
fileInput.addEventListener('change', function() {
    console.log('File input changed', fileInput.files);
    updateFileNameDisplay(fileInput.files);
});

// Update file name display on file drop
dropArea.addEventListener('drop', function(e) {
    var dt = e.dataTransfer;
    var files = dt.files;
    
    fileInput.files = files; // Assign dropped files to the file input
    console.log('Files dropped:', files);
    updateFileNameDisplay(files);
});

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function highlight(e) { dropArea.classList.add('highlight'); }
    function unhighlight(e) { dropArea.classList.remove('highlight'); }

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, unhighlight, false);
    });

    dropArea.addEventListener('drop', function(e) {
        var dt = e.dataTransfer;
        var files = dt.files;

        fileInput.files = files;
        console.log('Files dropped:', files);
    });

    dropArea.addEventListener('click', function() {
        fileInput.click();
    });

    fileInput.addEventListener('change', function() {
        console.log('File input changed', fileInput.files);
    });

    $('#uploadForm').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            type: 'POST',
            url: '{{ route("contacts.upload") }}',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                $('#step1').hide();
                $('#filePath').val(response.filePath);
                $('#csvHeaders').empty();
                response.headers.forEach(header => {
                    $('#csvHeaders').append(`<th>${header}<select name="mapping[${header}]" class="form-control column-mapping">
                        <option value="" disabled selected>Choose</option>
    @foreach ($dbColumns as $column)
        <option value="{{ $column }}">{{ $column }}</option>
    @endforeach
</select></th>`);
                });

                $('#csvPreview').empty();
                response.sampleData.forEach(row => {
                    var rowHtml = '<tr>';
                    row.forEach(cell => {
                        rowHtml += `<td>${cell}</td>`;
                    });
                    rowHtml += '</tr>';
                    $('#csvPreview').append(rowHtml);
                });
                $('#step2').show();
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });

    $('#toStep3').click(function() {
        $('#step2').hide();
        $('#step3').show();
    });

    // Function to serialize FormData into a URL-encoded string
function serializeFormData(formData) {
    const params = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
        params.append(key, value);
    }
    return params.toString();
}

    $('#detailsForm').submit(function(e) {
        e.preventDefault();
       // Initialize FormData with the form element
       var formData = new FormData(this);
    // Loop through each select element in the column mapping and add their values to formData
    $('.column-mapping').each(function() {
        var selectedValue = $(this).val();
        var name = $(this).attr('name');
        formData.append(name, selectedValue);
        console.log('Appending to formData:', name, '=', selectedValue);
    });

    // Serialize FormData
    var serializedData = serializeFormData(formData);
    console.log(serializedData);

$.ajax({
    type: 'POST',
    url: '{{ route("contacts.completeImport") }}', // Adjust the URL as necessary
    data: serializedData,
  
            success: function(response) {
                $('#importModal').modal('hide');
              //  alert('Import completed successfully!');
                // Reset and prepare for next import
                $('#uploadForm')[0].reset();
                $('#mappingForm')[0].reset();
                $('#detailsForm')[0].reset();
                $('#step3').hide();
                $('#step1').show();
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });

    // Any additional JavaScript logic required
     // Function to update dropdown options based on selections
     function updateDropdowns() {
        // First, enable all options
        $('.column-mapping option').prop('disabled', false);

        // Then, disable options that are already selected in other dropdowns
        $('.column-mapping').each(function() {
            var selectedValue = $(this).val();
            if (selectedValue) {
                $('.column-mapping').not(this).find(`option[value="${selectedValue}"]`).prop('disabled', true);
            }
        });
    }

    // Call updateDropdowns whenever any dropdown changes
    $(document).on('change', '.column-mapping', function() {
        updateDropdowns();
    });
});
</script>

</body>
</html>
