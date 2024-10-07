<?php
	# quizgeg ver fixed; ggn 2024
	# guizgeg — simple webapp for (mainly) repetition of learning words
	# (but, in fact, it can be used to repetition of any topics you need)

	# glossary:
	#	*	$_SESSION['current'] — array with currently learning words (10)
	#			[word in known language]	[word in foreign language]	[remaining repetitions]
	#	[row]	[string]					[string]					[int]
	#
	#	*	$systemResponse — text shown above the typing area, used for both help and responses if user is correct
	#
	#	*	$magicNumber — idk what is it, i just made it look kind of acceptable (it was as for now random number written multiple times in the code), 
	#	in some future commit i'll try to investigate how it works and change it to even more acceptable form if necessary
	#	(preferably much higher than number of items in file, unless you want to make your quiz unfinishable :>)
	#
	#	

	session_start();
	if(isset($_SESSION['current']))
		if(sizeof($_SESSION['current'])==0)
			header("Location:./?change");

	$magicNumber=2137;
	$mainFilename="material.txt";
	$explanationsFilename="tokiAll.txt";


	if(!isset($_SESSION['current'])||isset($_GET['change'])||sizeof($_SESSION['current'])==0)
	{
		loadMainFile($mainFilename);
	}

	function loadMainFile($fileName)
	{
			$mainFile=fopen($fileName, "r") or die("debilu znikaj pliku nie ma!");
			$lines=0;
			# check number of items in file
			while(!feof($mainFile))
			{
				if(fgets($mainFile))
				$lines++;
			}
			# creation of temporary $_SESSION['current'] array and load 
			$tab=array(array());
			fseek($mainFile,0);
			for($i=0; $i<$lines; $i++)
			{
				$currentlyProcessedLine=fgets($mainFile);
				$bothWords=array("","");
				$ii=0;
				while($currentlyProcessedLine[$ii]!="\t")
				{
					$bothWords[0]=$bothWords[0].$currentlyProcessedLine[$ii];
					$ii++;
				}
				while($ii<strlen($currentlyProcessedLine)-2)#2 because we are eliminating an \n at the end of line
				{
					$ii++;
					$bothWords[1]=$bothWords[1].$currentlyProcessedLine[$ii];
				}
				$tab[$i][0]=$bothWords[0];
				$tab[$i][1]=$bothWords[1];
				$tab[$i][2]=0;
			}
			$_SESSION['current']=array(array("","",0));
			for($i=0; $i<10; $i++)
			{
				$_SESSION['current'][$i]=$tab[array_rand($tab)];
			}
			header("Location:./");
	}

	
	$id=$magicNumber;
	$systemResponse="";
	$onClickMsg="";
	if(isset($_POST['id'])&&isset($_POST['ans']))
	{
		if(isset($explanationsFilename))
		{
			$explanationsFile=fopen($explanationsFilename,"r") or die("debilu znikaj pliku nie ma!");
			while(!feof($explanationsFile))
			{
				$currentlyProcessedLine=fgets($explanationsFile);
				$bothWords=array("","");
				$ii=0;
				while($currentlyProcessedLine[$ii]!="\t")
				{
					$bothWords[0]=$bothWords[0].$currentlyProcessedLine[$ii];
					$ii++;
				}
				while($ii<strlen($currentlyProcessedLine)-2)
				{
					$ii++;
					$bothWords[1]=$bothWords[1].$currentlyProcessedLine[$ii];
				}
				if($bothWords[0]==$_SESSION['current'][$_POST['id']][1])
				{
					$onClickMsg="<pre>$bothWords[1]</pre>".'<a href="https://linku.la/words/'.$bothWords[0].'" target="_blank">zobacz więcej na linku.la</a>';
					break;
				}
			}
		}
		if($_POST['ans']==$_SESSION['current'][$_POST['id']][1])
		{
			$_SESSION['current'][$_POST['id']][2]++;
			$systemResponse="dobrze!".'<br>'.$_SESSION['current'][$_POST['id']][0]."  =  ".'<span class="underline" id="showYourself">'.$_SESSION['current'][$_POST['id']][1]."</span><br>dobrych odpowiedzi: ".
			$_SESSION['current'][$_POST['id']][2];
			$sysResStyle="greenText";
		}
		else
		{
			$id=$_POST['id'];
			$systemResponse='poprawna odpowiedź: "'.'<span class="underline" id="showYourself">'.$_SESSION['current'][$_POST['id']][1].'</span>"<br>Twoja odpowiedź: "'.htmlentities($_POST['ans']).'"';
			$sysResStyle="purpleText";
			$_SESSION['current'][$_POST['id']][2]=0;
		}

		if($_SESSION['current'][$_POST['id']][2]>2)
		{
			if(sizeof($_SESSION['current'])==1)
			{
				header("Location:./skonczonaTalia.html");
			}
			for($i=0; $i<sizeof($_SESSION['current'])-1; $i++)
			{
				if($i>=$_POST['id'])
				{
					$_SESSION['current'][$i]=$_SESSION['current'][$i+1];
				}
			}
			unset($_SESSION['current'][sizeof($_SESSION['current'])-1]);
		}
	}


	if(isset($_GET['help']))
	{
		$id=$_GET['help'];
		$systemResponse=$_SESSION['current'][$id][1][0];//json_encode($_SESSION['current']);
		$sysResStyle="yellowText";
	}
	else if($id==$magicNumber)
	{
		if(isset($_POST['id']))
			$id=randomItem($_POST['id'],sizeof($_SESSION['current']),$magicNumber);
		else
			$id=randomItem($magicNumber,sizeof($_SESSION['current']),$magicNumber);
	}

	# mein gott, here is, probably, the error you are looking for, there is an recursion 💀
	function randomItem($a,$b,$magicNumber)
	{
		$id=array_rand($_SESSION['current']);
		if($a<$magicNumber&&$id==$a&&$b>1)
			$id=randomItem($a,$b,$magicNumber);
		return $id;
	}

	// print_r($_SESSION['current']);
	// unset($_SESSION['current']);
	// session_unset();
	// echo("<br><pre>".isset($_SESSION['current'])."</pre>");
	// print_r($tab[$_POST['id']]);
?>
<!DOCTYPE HTML>
<html lang="pl">
	<head>
		<meta charset="utf-8">
		<title>QuizGeg - lepszy Quizlet (bo za darmo i tylko pisanie)</title>
		<link rel="stylesheet" type="text/css" href="style.css?<?=time()?>">
		<?php
		/*<link rel="stylesheet" type="text/css" href="style_m.css?<?=time()?>">
		<link rel="stylesheet" type="text/css" href="style_m.css?<?=time()?>">*/
		?>
		<link rel="stylesheet" type="text/css" href="ggngnn.css?<?=time()?>">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	</head>
	<body>
		<header><h1>QuizGeg</h1><h3>lepszy quizlet (specjalnie z małej)</h3></header>
		<nav class="padd1p"><a href="readme.html">readme</a></nav>
		<main>
			<?php 
				if(strlen($onClickMsg)>0)
				{
					echo '<div class="onClickMsg padd05p darkgreyopacityDiv whiteText" id="onClickMsg"><span class="underline pointer redText" id="close">x</span><br>'.$onClickMsg.'</div>';
				}
				if(strlen($systemResponse)>0)
				{
					echo '<p class="'.$sysResStyle.'">'.$systemResponse.'</p>';
				}
			?>
			<?=$_SESSION['current'][$id][0]?>
			<form method="post" action="./">
				<input type="text" name="ans" autocomplete="off" autofocus>
				<input type="text" name="id" value="<?=$id?>" style="display:none;">
				<input type="submit">
			</form>
			<a href="?help=<?=$id?>">podpowiedz 1. literę</a>
			<a href="?change">zmień talię</a>
			<p>zostało w talii: <?=sizeof($_SESSION['current'])?></p>
		</main>
		<footer>
			by <a href="https://pbp.one.pl" target="_blank">ggn</a>.<br>v. \frac{∂(&alpha;x-y)}{∂x}<br>strona uzywa zmienne sesyjne dla przechowywania pojedynczej talii słów. nie przechowuje zadnych informacji mogących zindentyfikowac uzytkownika. <br><a href="https://github.com/gegangene/quizgeg" target="_blank">repo na githubie</a>
		</footer>
		<script>
			function hvr(whatToDo)
			{
				document.getElementById("onClickMsg").style.display=whatToDo;
			}
			document.getElementById("showYourself").addEventListener("click",function(){hvr("block")});
			document.getElementById("close").addEventListener("click",function(){hvr("none")});
		</script>
	</body>
</html>