<?php 
    require_once('mysql.php');
    require('functions.php'); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
</head>
<body>
    <?php 
        $tb_kriteria = array(
            'rata2' => ['jenis' => 'benefit', 'persentase' => 30],
            'jml_quiz' => ['jenis' => 'benefit', 'persentase' => 25],
            'retake_formatif' => ['jenis' => 'benefit', 'persentase' => 10],
            'retake_sumatif' => ['jenis' => 'cost', 'persentase' => 10],
            'rata2_waktu' => ['jenis' => 'cost', 'persentase' => 15],
            'rata2_selesai' => ['jenis' => 'cost', 'persentase' => 10]
        );
        $sql = "SELECT b.lastname, SUM(a.grade)/COUNT(a.id) AS rata2, t.jml_quiz, t2.retake_formatif, t3.retake_sumatif, t4.rata2_waktu, t5.rata2_selesai FROM `mdl_quiz_grades` a
                    INNER JOIN `mdl_quiz` c ON a.quiz = c.id
                    INNER JOIN `mdl_course` d ON d.id = c.course
                    INNER JOIN `mdl_context` e ON e.instanceid = d.id
                    INNER JOIN `mdl_role_assignments` f ON f.contextid = e.id
                    INNER JOIN mdl_user b ON f.userid = b.id
                    INNER JOIN `mdl_role` g ON g.id = f.roleid
                    LEFT JOIN (
                        SELECT e.id, COUNT(a.id) AS jml_quiz FROM `mdl_quiz` a 
                            INNER JOIN `mdl_course` b ON a.course = b.id
                            INNER JOIN `mdl_context` c ON c.instanceid = b.id
                            INNER JOIN `mdl_role_assignments` d ON d.contextid = c.id
                            INNER JOIN `mdl_user` e ON d.userid = e.id
                            INNER JOIN `mdl_role` f ON f.id = d.roleid
                            WHERE f.shortname = 'editingteacher' AND e.username <> 'admin'
                            GROUP BY e.id
                        ) t ON t.id = b.id
                    LEFT JOIN (
                        SELECT e.id, SUM(a.attempts)/COUNT(a.id) AS retake_formatif FROM `mdl_quiz` a 
                            INNER JOIN `mdl_course` b ON a.course = b.id
                            INNER JOIN `mdl_context` c ON c.instanceid = b.id
                            INNER JOIN `mdl_role_assignments` d ON d.contextid = c.id
                            INNER JOIN mdl_user e ON d.userid = e.id
                            INNER JOIN `mdl_role` f ON f.id = d.roleid
                            WHERE LEFT(a.name,1) = 1 AND f.shortname = 'editingteacher' AND e.username <> 'admin'
                            GROUP BY e.id
                        ) t2 ON t2.id = b.id
                    LEFT JOIN (
                        SELECT e.id, SUM(a.attempts)/COUNT(a.id) AS retake_sumatif FROM `mdl_quiz` a 
                            INNER JOIN `mdl_course` b ON a.course = b.id
                            INNER JOIN `mdl_context` c ON c.instanceid = b.id
                            INNER JOIN `mdl_role_assignments` d ON d.contextid = c.id
                            INNER JOIN mdl_user e ON d.userid = e.id
                            INNER JOIN `mdl_role` f ON f.id = d.roleid
                            WHERE LEFT(a.name,1) = 2 AND f.shortname = 'editingteacher' AND e.username <> 'admin'
                            GROUP BY e.id
                        ) t3 ON t3.id = b.id
                    LEFT JOIN (
                        SELECT e.id, (SUM(a.timelimit)/COUNT(a.id))/60 AS rata2_waktu FROM `mdl_quiz` a
                            INNER JOIN `mdl_course` b ON a.course = b.id
                            INNER JOIN `mdl_context` c ON c.instanceid = b.id
                            INNER JOIN `mdl_role_assignments` d ON d.contextid = c.id
                            INNER JOIN mdl_user e ON d.userid = e.id
                            INNER JOIN `mdl_role` f ON f.id = d.roleid
                            WHERE f.shortname = 'editingteacher' AND e.username <> 'admin'
                            GROUP BY e.id
                        ) t4 ON t4.id = b.id
                    LEFT JOIN (
                        SELECT e.id, SUM(z.timefinish-z.timestart)/COUNT(a.id) rata2_selesai FROM `mdl_quiz_attempts` z 
                            INNER JOIN `mdl_quiz` a ON z.quiz = a.id
                            INNER JOIN `mdl_course` b ON a.course = b.id
                            INNER JOIN `mdl_context` c ON c.instanceid = b.id
                            INNER JOIN `mdl_role_assignments` d ON d.contextid = c.id
                            INNER JOIN mdl_user e ON d.userid = e.id
                            INNER JOIN `mdl_role` f ON f.id = d.roleid
                            WHERE f.shortname = 'editingteacher' AND e.username <> 'admin'
                            GROUP BY e.id
                        ) t5 ON t5.id = b.id
                    WHERE g.shortname = 'editingteacher' AND b.username <> 'admin'
                    GROUP BY b.id";
        $data = $conn->query($sql);
        $data = $data->fetch_all(MYSQLI_ASSOC);
        
        $normalisasi = $data;
        foreach ($tb_kriteria as $key => $krit) {
            if ($krit['jenis'] == 'benefit') {
                for ($i=0; $i < count($normalisasi); $i++) { 
                    $normalisasi[$i][$key] = $normalisasi[$i][$key]/ getAttribute($data, $key)['max'];
                }
            } else {
                for ($i=0; $i < count($normalisasi); $i++) { 
                    $normalisasi[$i][$key] = getAttribute($data, $key)['min']/ $normalisasi[$i][$key];
                }
            }
        }
        $hasil = $normalisasi;

        for ($i=0; $i < count($hasil); $i++) {
            $hasil[$i]['n_bobot'] = 0;
        }
        
        foreach ($tb_kriteria as $key => $krit) {
            for ($i=0; $i < count($hasil); $i++) {
                $hasil[$i]['n_bobot'] += $hasil[$i][$key] * $krit['persentase']/100;
            }
        }

        $hasil = sortData($hasil);
    ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm">
                <h3>Analisis</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Jumlah <br>mahasiswa <br>yg memahami materi</th>
                            <th>Jumlah <br>Quiz <br>yang <br>diberikan</th>
                            <th>Jumlah <br>Kesempatan <br>Retake <br>(sumatif)</th>
                            <th>Jumlah <br>Kesempatan <br>Retake <br>(formatif)</th>
                            <th>Rata-rata <br>waktu <br>yang <br>diberikan</th>
                            <th>Rata-rata <br>waktu <br>penyelesaian <br>quiz</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $key => $h) { ?>
                        <tr>
                            <td><?php echo $h['lastname']; ?></td>
                            <td><?php echo number_format($h['rata2'],2,'.',','); ?></td>
                            <td><?php echo $h['jml_quiz']; ?></td>
                            <td><?php echo $h['retake_sumatif']; ?></td>
                            <td><?php echo $h['retake_formatif']; ?></td>
                            <td><?php echo number_format($h['rata2_waktu'],2,'.',','); ?></td>
                            <td><?php echo number_format($h['rata2_selesai'],2,'.',','); ?></td>
                        </tr>
                        <?php }?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-sm">
                <h3>Normalisasi</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Jumlah <br>mahasiswa <br>yg memahami materi</th>
                            <th>Jumlah <br>Quiz <br>yang <br>diberikan</th>
                            <th>Jumlah <br>Kesempatan <br>Retake <br>(sumatif)</th>
                            <th>Jumlah <br>Kesempatan <br>Retake <br>(formatif)</th>
                            <th>Rata-rata <br>waktu <br>yang <br>diberikan</th>
                            <th>Rata-rata <br>waktu <br>penyelesaian <br>quiz</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($normalisasi as $key => $h) { ?>
                        <tr>
                            <td><?php echo $h['lastname']; ?></td>
                            <td><?php echo number_format($h['rata2'],2,'.',','); ?></td>
                            <td><?php echo $h['jml_quiz']; ?></td>
                            <td><?php echo $h['retake_sumatif']; ?></td>
                            <td><?php echo $h['retake_formatif']; ?></td>
                            <td><?php echo number_format($h['rata2_waktu'],2,'.',','); ?></td>
                            <td><?php echo number_format($h['rata2_selesai'],2,'.',','); ?></td>
                        </tr>
                        <?php }?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-sm">
                <h3>Perankingan</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Jumlah <br>mahasiswa <br>yg memahami materi</th>
                            <th>Jumlah <br>Quiz <br>yang <br>diberikan</th>
                            <th>Jumlah <br>Kesempatan <br>Retake <br>(sumatif)</th>
                            <th>Jumlah <br>Kesempatan <br>Retake <br>(formatif)</th>
                            <th>Rata-rata <br>waktu <br>yang <br>diberikan</th>
                            <th>Rata-rata <br>waktu <br>penyelesaian <br>quiz</th>
                            <th>Nilai <br>Bobot</th>
                            <th>Ranking</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hasil as $key => $h) { ?>
                        <tr>
                            <td><?php echo $h['lastname']; ?></td>
                            <td><?php echo number_format($h['rata2'],2,'.',','); ?></td>
                            <td><?php echo $h['jml_quiz']; ?></td>
                            <td><?php echo $h['retake_sumatif']; ?></td>
                            <td><?php echo $h['retake_formatif']; ?></td>
                            <td><?php echo number_format($h['rata2_waktu'],2,'.',','); ?></td>
                            <td><?php echo number_format($h['rata2_selesai'],2,'.',','); ?></td>
                            <td><?php echo number_format($h['n_bobot'],2,'.',','); ?></td>
                            <td><?php echo $key+1; ?></td>
                        </tr>
                        <?php }?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <?php
        echo '<script>';
        echo 'console.log("Analisis:");';
        echo 'console.log('. json_encode( $data ) .');';
        echo 'console.log("Normalisasi:");';
        echo 'console.log('. json_encode( $normalisasi ) .');';
        echo 'console.log("Perankingan:");';
        echo 'console.log('. json_encode( $hasil ) .');';
        echo '</script>';
    ?>
</body>
</html>