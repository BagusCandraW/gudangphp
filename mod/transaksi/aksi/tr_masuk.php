<?php
include"../../../config/koneksi.php";

$tgl			=$_POST['tgl'];
$kd_tr_masuk	=$_POST['kd_tr_masuk'];
$nm_barang		=$_POST['nm_barang'];
$kd_barang		=$_POST['kd_barang'];
$kd_satuan		=$_POST['kd_satuan'];
$satuan			=$_POST['satuan'];
$id_satuan		=$_POST['id_satuan'];
$jumlah			=$_POST['jumlah'];
$kd_tr			=$kd_tr_masuk;
$aksi			=$_POST['aksi'];

if($aksi == ""){
		$brg=mysql_query("SELECT sum(jml_konversi) as jum_konv,kd_barang from td_brg_masuk where id='$_GET[id]'");
		$jml_stk_awal=mysql_fetch_array($brg); 
		$jm_brg_awal = $jml_stk_awal['jum_konv'];
		$kd_barang = $jml_stk_awal['kd_barang'];

		$brg1=mysql_query("SELECT jml_stok from barang where id='$kd_barang'");
		$jml_stk_awal1=mysql_fetch_array($brg1); 
		$jm_stok = $jml_stk_awal1['jml_stok'];

		$jum_stok_akhir = $jm_stok-$jm_brg_awal;

		//echo"update barang set jml_stok = '$jum_stok_akhir' where id='$_GET[id]'";

		$delete=mysql_query("update barang set jml_stok = '$jum_stok_akhir' where id='$_GET[id]'");		

		$delete1=mysql_query("Delete from tr_brg_masuk where id='$_GET[id]'");

		$delete2=mysql_query("Delete from td_brg_masuk where id='$_GET[id]'");

		
		$no_max=mysql_query("SELECT ifnull(max(no),0)+1 AS reg  FROM tr_brg_masuk");
		$n=mysql_fetch_array($no_max);   

		$no = $n[reg];

		//insert header transaksi barang masuk tr_brg_masuk
		$insert1=mysql_query("insert into tr_brg_masuk(id,tgl_masuk,no)
							  values('$kd_tr_masuk','$tgl','$no')");

		for($i=1;$i<count($nm_barang);$i++){
			$jm	= substr($satuan[$i] , 0 ,1);
			//id 
			$kd = $kd_tr.$i; 
			
			//echo"$jm | $jumlah[$i] | $kd_satuan[$i] |";
			//nilai konversi ke satuan terkecil
			if($jm == "B"){
				  $jmlb=mysql_query("SELECT b.st_besar,c.jumlah AS jml_besar,b.st_sedang,d.jumlah AS jml_sedang,b.st_kecil,e.jumlah AS jml_kecil 
									FROM satuan b 
									INNER JOIN besar c ON b.st_besar=c.id 
									INNER JOIN sedang d ON b.st_sedang=d.id 
									INNER JOIN kecil e ON b.st_kecil=e.id WHERE b.id_satuan='$id_satuan[$i]'");
				  $n_jmlb	=mysql_fetch_array($jmlb); 
				  $nl_sedang= $n_jmlb['jml_sedang'];
				  $nl_kecil = $n_jmlb['jml_kecil'];
				  $nl_hsl1 	= $jumlah[$i]*$nl_sedang;
				  $nl_hsl2	= $nl_hsl1*$nl_kecil;
				  $nl_konv	= $nl_hsl2;
				/*
					echo"SELECT b.st_besar,c.jumlah AS jml_besar,b.st_sedang,d.jumlah AS jml_sedang,b.st_kecil,e.jumlah AS jml_kecil 
									FROM satuan b 
									INNER JOIN besar c ON b.st_besar=c.id 
									INNER JOIN sedang d ON b.st_sedang=d.id 
									INNER JOIN kecil e ON b.st_kecil=e.id WHERE b.id_satuan='$id_satuan[$i]'|$nl_konv";
			*/
			}else if($jm == "S"){
				 $jmlx=mysql_query("SELECT b.st_sedang,d.jumlah,b.st_kecil,e.jumlah FROM satuan b 
									INNER JOIN sedang d ON b.st_sedang=d.id
									INNER JOIN kecil e ON b.st_kecil=e.id where b.id_satuan='$id_satuan[$i]'");
				$n_jml=mysql_fetch_array($jmlx); 
				
				$nl_kecil  = $n_jml['jumlah'];
				
				$nl_hsl = $jumlah[$i]*$nl_kecil;
				$nl_konv= $nl_hsl;
				//echo"$nl_konv";
			}else if($jm == "K"){
				$nl_konv = $jumlah[$i];
				//echo"$nl_konv";
			}
			
			//insert transaksi detail barang masuk
			$insert1=mysql_query("insert into td_brg_masuk(kd_tr,id,kd_barang,satuan,kd_satuan,jumlah,jml_konversi)
							  values('$kd','$kd_tr_masuk','$kd_barang[$i]','$kd_satuan[$i]','$satuan[$i]','$jumlah[$i]','$nl_konv')");
			
			//update stok barang				  
			 $brg=mysql_query("SELECT * from barang where id='$kd_barang[$i]'");
			 $jml_stk_awal=mysql_fetch_array($brg); 
			 $jm_brg_awal = $jml_stk_awal['jml_stok'];
			 
			 $jm_stok	= $jm_brg_awal+$nl_konv;
			 $insert1=mysql_query("update barang set jml_stok = '$jm_stok' where id='$kd_barang[$i]'");				  
			/*echo"update barang set jml_stok = '$jm_stok' where id='$kd_barang[$i]' || $jm_brg_awal || $kd_barang[$i] || $jm_brg || 
			SELECT sum(jml_konversi) from td_brg_masuk where id='$kd_barang[$i]'
			";*/
		}


		if($insert1){
				echo "<script type=\"text/javascript\">
					  alert('Data berhasil tersimpan..');
				 </script>";
				
				echo "<script type=\"text/javascript\">
							   window.location = \"http://localhost/gudang/media.php?mod=brg_masuk\";	
					   </script>	
						";
			}else{
				echo "<script type=\"text/javascript\">
					  alert('Data gagal tersimpan..');
					  window.location = \"http://localhost/gudang/media.php?mod=brg_masuk\ \";
				  </script>";
			}
}else{	
		$no_max=mysql_query("SELECT ifnull(max(no),0)+1 AS reg  FROM tr_brg_masuk");
		$n=mysql_fetch_array($no_max);   

		$no = $n[reg];

		//insert header transaksi barang masuk tr_brg_masuk
		$insert1=mysql_query("insert into tr_brg_masuk(id,tgl_masuk,no)
							  values('$kd_tr_masuk','$tgl','$no')");

		for($i=1;$i<count($nm_barang);$i++){
			$jm	= substr($satuan[$i] , 0 ,1);
			//id 
			$kd = $kd_tr.$i; 
			
			//echo"$jm | $jumlah[$i] | $kd_satuan[$i] |";
			//nilai konversi ke satuan terkecil
			if($jm == "B"){
				  $jmlb=mysql_query("SELECT b.st_besar,c.jumlah AS jml_besar,b.st_sedang,d.jumlah AS jml_sedang,b.st_kecil,e.jumlah AS jml_kecil 
									FROM satuan b 
									INNER JOIN besar c ON b.st_besar=c.id 
									INNER JOIN sedang d ON b.st_sedang=d.id 
									INNER JOIN kecil e ON b.st_kecil=e.id WHERE b.id_satuan='$id_satuan[$i]'");
				  $n_jmlb	=mysql_fetch_array($jmlb); 
				  $nl_sedang= $n_jmlb['jml_sedang'];
				  $nl_kecil = $n_jmlb['jml_kecil'];
				  $nl_hsl1 	= $jumlah[$i]*$nl_sedang;
				  $nl_hsl2	= $nl_hsl1*$nl_kecil;
				  $nl_konv	= $nl_hsl2;
				/*
					echo"SELECT b.st_besar,c.jumlah AS jml_besar,b.st_sedang,d.jumlah AS jml_sedang,b.st_kecil,e.jumlah AS jml_kecil 
									FROM satuan b 
									INNER JOIN besar c ON b.st_besar=c.id 
									INNER JOIN sedang d ON b.st_sedang=d.id 
									INNER JOIN kecil e ON b.st_kecil=e.id WHERE b.id_satuan='$id_satuan[$i]'|$nl_konv";
			*/
			}else if($jm == "S"){
				 $jmlx=mysql_query("SELECT b.st_sedang,d.jumlah,b.st_kecil,e.jumlah FROM satuan b 
									INNER JOIN sedang d ON b.st_sedang=d.id
									INNER JOIN kecil e ON b.st_kecil=e.id where b.id_satuan='$id_satuan[$i]'");
				$n_jml=mysql_fetch_array($jmlx); 
				
				$nl_kecil  = $n_jml['jumlah'];
				
				$nl_hsl = $jumlah[$i]*$nl_kecil;
				$nl_konv= $nl_hsl;
				//echo"$nl_konv";
			}else if($jm == "K"){
				$nl_konv = $jumlah[$i];
				//echo"$nl_konv";
			}
			
			//insert transaksi detail barang masuk
			$insert1=mysql_query("insert into td_brg_masuk(kd_tr,id,kd_barang,satuan,kd_satuan,jumlah,jml_konversi)
							  values('$kd','$kd_tr_masuk','$kd_barang[$i]','$kd_satuan[$i]','$satuan[$i]','$jumlah[$i]','$nl_konv')");
			
			//update stok barang				  
			 $brg=mysql_query("SELECT * from barang where id='$kd_barang[$i]'");
			 $jml_stk_awal=mysql_fetch_array($brg); 
			 $jm_brg_awal = $jml_stk_awal['jml_stok'];
			 
			 $jm_stok	= $jm_brg_awal+$nl_konv;
			 $insert1=mysql_query("update barang set jml_stok = '$jm_stok' where id='$kd_barang[$i]'");				  
			/*echo"update barang set jml_stok = '$jm_stok' where id='$kd_barang[$i]' || $jm_brg_awal || $kd_barang[$i] || $jm_brg || 
			SELECT sum(jml_konversi) from td_brg_masuk where id='$kd_barang[$i]'
			";*/
		}


		if($insert1){
				echo "<script type=\"text/javascript\">
					  alert('Data berhasil tersimpan..');
				 </script>";
				
				echo "<script type=\"text/javascript\">
							   window.location = \"http://localhost/gudang/media.php?mod=brg_masuk\";	
					   </script>	
						";
			}else{
				echo "<script type=\"text/javascript\">
					  alert('Data gagal tersimpan..');
					  window.location = \"http://localhost/gudang/media.php?mod=brg_masuk\ \";
				  </script>";
			}
}
?>