<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <title>{site_title}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- Font Awesome CSS -->
  <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"> -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <!-- CSS của select2 -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

  <link rel="stylesheet" href="{base_url}css/main.css">
</head>

<body>
  <!-- Global message display block -->
  <div style="display: {globalmess}">
    {error}
    <div class="alert alert-danger">
      {mess}
    </div>
    {/error}

    {success}
    <div class="alert alert-success">
      {mess}
    </div>
    {/success}
  </div>
