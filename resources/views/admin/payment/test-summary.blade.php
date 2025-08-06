<!DOCTYPE html>
<html>
<head>
    <title>Test Payment Summary</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Test Payment Summary</h1>
    
    <div id="summary-results">
        <h3>Summary Data:</h3>
        <div id="summary-content">Loading...</div>
    </div>

    <div id="test-results">
        <h3>Test Results:</h3>
        <div id="test-content">Loading...</div>
    </div>

    <script>
        $(document).ready(function() {
            // Test summary endpoint
            $.ajax({
                url: '/admin/payments/summary',
                type: 'GET',
                success: function(response) {
                    $('#summary-content').html('<pre>' + JSON.stringify(response, null, 2) + '</pre>');
                },
                error: function(xhr, status, error) {
                    $('#summary-content').html('Error: ' + error + '<br>Response: ' + xhr.responseText);
                }
            });

            // Test simple endpoint
            $.ajax({
                url: '/admin/payments/test-summary',
                type: 'GET',
                success: function(response) {
                    $('#test-content').html('<pre>' + JSON.stringify(response, null, 2) + '</pre>');
                },
                error: function(xhr, status, error) {
                    $('#test-content').html('Error: ' + error + '<br>Response: ' + xhr.responseText);
                }
            });
        });
    </script>
</body>
</html>
