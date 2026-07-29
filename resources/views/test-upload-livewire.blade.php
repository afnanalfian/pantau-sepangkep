<!DOCTYPE html>
<html>
<head>
    <title>Test Upload</title>
</head>
<body>
    <h2>Test Upload Manual</h2>
    <form action="/test-upload-manual" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="date" name="tanggal" value="{{ date('Y-m-d') }}">
        <input type="file" name="file" accept=".xlsx,.xls">
        <button type="submit">Upload</button>
    </form>
</body>
</html>