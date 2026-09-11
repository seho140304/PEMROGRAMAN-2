<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Latihan 4</title>
</head>
<body>
    

    <?php
        $nama_hari = date("l");
        $day = "";
        if ($nama_hari == "Sunday") {
            $day = "Jum'at";
        } elseif ($nama_hari == "Monday") {
            echo "Meeting awal minggu";
        } elseif ($nama_hari == "Wednesday") {
            echo "Hari ini adalah hari Rabu";
        } elseif ($nama_hari == "Thursday") {
            echo "Hari ini adalah hari Kamis";
        } elseif ($nama_hari == "Friday") {
            echo "Hari ini adalah hari Jumat";
        } elseif ($nama_hari == "Saturday") {
            echo "Hari ini adalah hari Sabtu";
        } else {
            echo "Hari ini adalah hari Minggu";
        }

        echo "<h1>Hari ini adalah hari : $day </h1>";
        
        switch($nama_hari) {
            case "Sunday":
                print("Sunday");
                echo "Waktu istirahat";
                break;

            case "Monday":
                print("Senin <br>");
                print "Meeting awal minggu";
                break;

            case "Wednesday":
                echo "Hari ini adalah hari Rabu";
                break;

            case "Thursday":
                echo "Hari ini adalah hari Kamis";
                break;

            case "Friday":
                echo "Hari ini adalah hari Jumat";
                break;

            case "Saturday":
                echo "Hari ini adalah hari Sabtu";
                break;

            case "Sunday":
                echo "Hari ini adalah hari Minggu";
                break;

            default:
                echo "Tidak ada hari yang cocok";
        }
    
    ?>
</body>
</html>