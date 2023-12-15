<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMTrace Online Tool (CMTOT)</title>

    <style>
        body
        {
            margin: 0;
            font-family: "Lucida Console";
        }

        div.header
        {
            width: 100%;      
        }

        div.header > *
        {
            margin-right: 10px;
            margin-left: 10px;
        }

        div.cpl
        {
            padding-top: 3px;
            padding-bottom: 3px;
        }

        .fileMessage
        {
            margin-right: 10px;
            margin-left: 10px;
        }

        div.logBlockList
        {
            width: 100%;   
        }

        div.logBlock
        {
            line-break: anywhere;
            padding-top: 3px;
            padding-bottom: 3px;
            margin-right: 10px;
            margin-left: 10px;
        }

        div.logBlock:not(:last-child)
        {
            border-bottom: 1px solid black;
            border-top: 1px solid black;
        }

        div.logBlock:hover
        {
            padding-left: 20px;
        }

        div.logBlock[data-type="1"]
        {
            background-color: #FFFFFF;
        }
        div.logBlock[data-type="2"]
        {
            background-color: #FFFF00;
        }
        div.logBlock[data-type="3"]
        {
            background-color: #FF0000;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CMTrace Online Tool (CMTOT)</h1>
        <h4>By Oscar Freij</h4>
        <br>
        <div class="cpl">
            <h3>Controll Panel</h3>
            <form action="" method="post" enctype="multipart/form-data">
                <lable for=logFile_Input">Selected Logfile:</lable>
                <input type="file" name="logFile_Input" id="logFile_Input">
                <br>
                <input type="submit" value="View Formated Log File">
            </form>
        </div>
    </div>
    <br>
    <?php
    if (isset($_FILES['logFile_Input']) && $_FILES['logFile_Input']['size'] > 0)
    {
        // Check File Size //
        if ($_FILES["logFile_Input"]["size"] > 5000000) {
            ?>
            <h4 class="fileMessage">Sorry, your file is too large. Size limit: 5MB</h4>
            <?php
            die;
        }

        // Allow certain file formats //
        if(!str_ends_with($_FILES['logFile_Input']['name'],".log")) {
            ?>
            <h4 class="fileMessage">Sorry, only .log files are allowed.</h4>
            <?php
            die;
        }

        foreach ($_FILES as $key => $fileData) {
            ?>
            <h4 class="fileMessage">Displaying: <?=$fileData['name']?></h4>
            <div class="logBlockList">
            <?php
            $logBlockSplitString = "<![LOG[";
            $logBlockDataEndString = "]LOG]!>";

            $logBlocks = explode($logBlockSplitString, file_get_contents($fileData['tmp_name']));

            foreach ($logBlocks as $id => $block)
            {
                
                if ($id == 0)
                {
                    // Skip first block as it is always empty //
                    continue;
                }
                $blockData = substr($block,0,strpos($block,$logBlockDataEndString));
                preg_match_all('/\"(.*?)\"/',substr($block,strpos($block,$logBlockDataEndString)+strlen($logBlockDataEndString)),$blockSubDataRaw);
                
                $blockSubData = ["time" => str_replace('"','',$blockSubDataRaw[0][0]), "date" => str_replace('"','',$blockSubDataRaw[0][1]), "component" => str_replace('"','',$blockSubDataRaw[0][2]) ?: 'NULL', "context" => str_replace('"','',$blockSubDataRaw[0][3]) ?: 'NULL', "type" => str_replace('"','',$blockSubDataRaw[0][4]), "thread" => str_replace('"','',$blockSubDataRaw[0][5]), "file" => str_replace('"','',$blockSubDataRaw[0][6]) ?: 'NULL'];

                ?>
                <div class="logBlock" data-type="<?=$blockSubData['type']?>">
                    <?php
                    echo("<span>");
                    echo(str_replace(array("\t","\\t"),"&ensp;",str_replace(array("\r\n","\n","\\r\\n","\\n"),"<br>",$blockData)));
                    echo("</span>");
                    echo("<br>");
                    echo("<span>");
                    echo($blockSubData['date']."&nbsp;@&nbsp;".substr($blockSubData['time'], 0, strpos($blockSubData['time'], '.')));
                    echo("&ensp;/&ensp;");
                    echo("Component: ".$blockSubData['component']);
                    echo("&ensp;/&ensp;");
                    echo("Context: ".$blockSubData['context']);
                    echo("&ensp;/&ensp;");
                    echo("Thread: ".$blockSubData['thread']."(0x".base_convert($blockSubData['thread'], 10, 16)).")";
                    echo("&ensp;/&ensp;");
                    echo("File: ".$blockSubData['file']);
                    echo("</span>");
                    ?>
                </div>
                <?php
            }
            
            ?>
            </div>
            <?php
        }           
    }
    else
    {
        ?>
            <h4 style="margin-right: 10px; margin-left: 10px;">No file selected!</h4>
        <?php
    }
    ?>
</body>
</html>