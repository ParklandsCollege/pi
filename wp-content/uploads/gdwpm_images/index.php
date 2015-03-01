<?php
if (isset($_GET['imgid'])){
	$gdwpm_ekst_gbr = explode('.', $_GET['imgid']);
	if($gdwpm_ekst_gbr[1] == 'png' || $gdwpm_ekst_gbr[1] == 'gif' || $gdwpm_ekst_gbr[1] == 'bmp'){
		header("Content-Type: image/" . $gdwpm_ekst_gbr[1]);
	}else{
		header("Content-Type: image/jpg");
	}
	$gdurl = "https://docs.google.com/uc?id=" . $gdwpm_ekst_gbr[0] . "&export=view";
	@readfile($gdurl);
}
?>