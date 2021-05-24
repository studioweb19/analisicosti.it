<html>
<body>

<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">

<table id="example" class="display" cellspacing="0" width="100%">
    <thead>
    <tr>
        <th>ID</th>
        <th>Cognome</th>
        <th>Nome</th>
        <th>Indirizzo</th>
        <th>CAP</th>
    </tr>
    </thead>
</table>
<script src="//code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#example').DataTable( {
            "processing": true,
            "serverSide": true,
            "ajax": "server_side_clienti.php"
        } );
    } );
</script>
</body>
</html>