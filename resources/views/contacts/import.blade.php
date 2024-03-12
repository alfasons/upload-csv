<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Import Contacts</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#importModal">Import Contacts</button>

    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Contacts</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Step 1: Upload CSV -->
                    <div class="import-step" id="step1">
                        <form id="uploadForm">
                            <div class="form-group">
                                <label for="csv_file">Upload CSV file</label>
                                <input type="file" class="form-control-file" id="csv_file" name="csv_file" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </form>
                    </div>
                    <!-- Step 2: Map Columns -->
                    <div class="import-step" id="step2" style="display: none;">
                        <h4>Map Columns</h4>
                        <div class="table table-responsive">
                            <form id="mappingForm">
                            <!--    <input type="hidden" name="filePath" id="filePath">-->
                                <table class="table">
                                    <thead>
                                        <tr id="csvHeaders"></tr>
                                    </thead>
                                    <tbody id="csvPreview"></tbody>
                                </table>
                                <button type="button" id="toStep3" class="btn btn-success">Next</button>
                            </form>
                        </div>
                    </div>
                    <!-- Step 3: Additional Fields -->
                    <div class="import-step" id="step3" style="display: none;">
                        <h4>Add Details</h4>
                        <form id="detailsForm">
                            <input type="hidden" name="filePath" id="filePath">
                            <div class="form-group">
                                <label for="field1">Field 1</label>
                                <input type="text" class="form-control" id="field1" name="field1" required>
                            </div>
                            <div class="form-group">
                                <label for="field2">Field 2</label>
                                <input type="text" class="form-control" id="field2" name="field2" required>
                            </div>
                            <div class="form-group">
                                <label for="field3">Field 3</label>
                                <input type="text" class="form-control" id="field3" name="field3" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Complete</button>
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

<script>
$(document).ready(function() {
    // Set up AJAX headers with CSRF token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
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
