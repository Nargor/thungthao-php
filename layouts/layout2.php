<?php
$config=getConfig();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="/api/readfile/js.php?file=cookie"></script>

    <?= $layout->getHead() ?>
</head>

<body class="">
    <?php include __DIR__ . '/../components/navbar.php'; ?>
   
        <h1>
            This is a layout 2
        </h1>
        <?= $layout->getContent() ?>
   

    

   
</body>

</html>