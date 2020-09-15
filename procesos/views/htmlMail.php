<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

</head>

<body>
<pre>
    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <td>ID</td>
                <td>Reference</td>
                <td>Name</td>
                <td>Subject</td>
                <td>Status</td>
                <td>Opened</td>
                <td>Last Update</td>
            </tr>
        </thead>
        <tbody>
            <?php foreach($data as $d):?>
                <tr>
                    <td><?php echo $d["ID"]?></td>
                    <td><?php echo $d["reference"]?></td>
                    <td><?php echo $d["name"]?></td>
                    <td><?php echo $d["subject"]?></td>
                    <td><?php echo $d["estatus"]?></td>
                    <td><?php echo $d["opened"]?></td>
                    <td><?php echo $d["lastupdate"]?></td>
                </tr>
            <?php endforeach;?>
        </tbody>
    </table>
</pre>
</body>
</html>