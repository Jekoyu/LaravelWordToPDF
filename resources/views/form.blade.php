<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Word</title>
</head>
<body>
    <h2>Generate Dokumen Word</h2>
    <form action="{{ route('generate-pdf') }}" method="POST">
        @csrf
        <label>Nama Asesi:</label>
        <input type="text" name="asesi" required><br>

        <label>Nama Asesor:</label>
        <input type="text" name="asesor" required><br>

        <button type="submit">Generate Pdf</button>
    </form>
</body>
</html>
-