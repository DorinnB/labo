<?php			//connection sql
	Require("../fonctions.php");
	Connectionsql();

if (isset($_GET['n_fichier']) AND $_GET['n_fichier']!="" AND $_GET['n_fichier']>1)	{
							//recuperation des donnees de l'essai
	$req='
		SELECT prefixe, nom_eprouvette, drawing, material, dessin, machine, enregistreur, compresseur, i1.ind_temp as ind_temp_top, i2.ind_temp as ind_temp_strap, i3.ind_temp as ind_temp_bot, extensometre, chauffage, type_chauffage, enregistreur, acquisition, cartouche_load, cartouche_stroke, cartouche_strain,
			customer, job, split, n_essai, n_fichier, DATE_FORMAT(enregistrementessais.date,"%d %b %Y") as date, t1.technicien as operateur, t2.technicien as controleur, eprouvettes.c_temperature, c_frequence, eprouvettes.waveform, c_cycle_STL, c_Frequence_STL, runout, type_essai,
			c_type_1_val,c_type_2_val,
			type1.consigne_type as c_type_1, type2.consigne_type as c_type_2, c_unite,
			Lo, type, dim_1, dim_2, dim_3, Cycle_min, tbljobs.waveform as c_waveform
			FROM enregistrementessais
			LEFT JOIN eprouvettes ON enregistrementessais.id_eprouvette = eprouvettes.id_eprouvette
			LEFT JOIN tbljobs ON eprouvettes.id_job = tbljobs.id_tbljob
			LEFT JOIN info_jobs ON info_jobs.id_info_job=tbljobs.id_info_job
			LEFT JOIN type_essais ON type_essais.id_type_essai = tbljobs.id_type_essai
			LEFT JOIN dessins ON dessins.id_dessin=tbljobs.id_dessin
			LEFT JOIN acquisitions ON acquisitions.id_acquisition = enregistrementessais.id_acquisition
			LEFT JOIN techniciens t1 ON t1.id_technicien=enregistrementessais.id_operateur
			LEFT JOIN techniciens t2 ON t2.id_technicien=enregistrementessais.id_controleur
			LEFT JOIN postes ON postes.id_poste=enregistrementessais.id_poste
			LEFT JOIN machines ON machines.id_machine=postes.id_machine
			LEFT JOIN enregistreurs ON enregistreurs.id_enregistreur=postes.id_enregistreur
			LEFT JOIN extensometres ON extensometres.id_extensometre=postes.id_extensometre
			LEFT JOIN outillages o1 ON o1.id_outillage = postes.id_outillage_top
			LEFT JOIN outillages o2 ON o2.id_outillage = postes.id_outillage_bot
			LEFT JOIN chauffages ON chauffages.id_chauffage=postes.id_chauffage
			LEFT JOIN ind_temps i1 ON i1.id_ind_temp = postes.id_ind_temp_top
			LEFT JOIN ind_temps i2 ON i2.id_ind_temp = postes.id_ind_temp_strap
			LEFT JOIN ind_temps i3 ON i3.id_ind_temp = postes.id_ind_temp_bot
			LEFT JOIN consigne_types as type1 ON type1.id_consigne_type=tbljobs.c_1 
			LEFT JOIN consigne_types as type2 ON type2.id_consigne_type=tbljobs.c_2 										
			WHERE n_fichier ='.$_GET['n_fichier'].'
			;';
		//	echo $req;
		$req_essai = $db->query($req) or die (mysql_error());
		$tbl_essai = mysqli_fetch_assoc($req_essai);

		
		
	//traitement des donnees
	
	if (isset($tbl_essai['split']))		//groupement du nom du job avec ou sans indice
		$jobcomplet= $tbl_essai['customer'].'-'.$tbl_essai['job'].'-'.$tbl_essai['split'];
	else
		$jobcomplet= $tbl_essai['customer'].'-'.$tbl_essai['job'];
		
	if (isset($tbl_essai['prefixe']))		//groupement du nom d eprouvette avec ou sans préfixe
		$identification= $tbl_essai['prefixe'].'-'.$tbl_essai['nom_eprouvette'];
	else
		$identification= $tbl_essai['nom_eprouvette'];

	if (isset($tbl_essai['compresseur']) AND $tbl_essai['compresseur']==1)
		$compresseur="n";
	else
		$compresseur="o";	
	
	$tbl_essai['ind_temp_top'] = (isset($tbl_essai['ind_temp_top']))? $tbl_essai['ind_temp_top'] : "";
	$tbl_essai['ind_temp_strap'] = (isset($tbl_essai['ind_temp_strap']))? $tbl_essai['ind_temp_strap'] : "";
	$tbl_essai['ind_temp_bot'] = (isset($tbl_essai['ind_temp_bot']))? $tbl_essai['ind_temp_bot'] : "";
	if ($tbl_essai['ind_temp_top'] == $tbl_essai['ind_temp_bot'] )	{		//groupement des ind.temp.
		if ($tbl_essai['ind_temp_top'] == $tbl_essai['ind_temp_strap'])
			$ind_temp = $tbl_essai['ind_temp_top'];
		else
			$ind_temp = $tbl_essai['ind_temp_top'].'/'.$tbl_essai['ind_temp_strap'];
	}
	else
		$ind_temp = $tbl_essai['ind_temp_top'].'/'.$tbl_essai['ind_temp_strap'].'/'.$tbl_essai['ind_temp_bot'];
	
	if (isset($tbl_essai['type_chauffage']) AND $tbl_essai['type_chauffage']=="Coil")	//chauffage coil
		$coil=$tbl_essai['chauffage'];
	else
		$coil="";
	
	if (isset($tbl_essai['type_chauffage']) AND $tbl_essai['type_chauffage']=="Four")	//chauffage coil
		$four=$tbl_essai['chauffage'];
	else
		$four="";
		
	if (isset($tbl_essai['c_cycle_STL']) AND $tbl_essai['c_cycle_STL']!="0")	//STL
		$STL=$tbl_essai['c_cycle_STL'];
	else
		$STL="";
	
	if (isset($tbl_essai['c_Frequence_STL']) AND $tbl_essai['c_Frequence_STL']!="0")	//STL
		$F_STL=$tbl_essai['c_Frequence_STL'];
	else
		$F_STL="";
	
	if (isset($tbl_essai['runout']) AND $tbl_essai['runout']!="0")	//Runout
		$runout=$tbl_essai['runout'];
	else
		$runout="RTF";

	
//	if (isset($tbl_essai['Cycle_min']) AND $tbl_essai['Cycle_min']!="0")	//STL
//		$tbl_essai['Cycle_min']=$tbl_essai['Cycle_min'];
//	else
//		$tbl_essai['Cycle_min']="-";
	
	
	niveaumaxmin($tbl_essai['c_type_1'], $tbl_essai['c_type_2'], $tbl_essai['c_type_1_val'], $tbl_essai['c_type_2_val']);
	$nb_dim=nb_dim($tbl_essai['type']);
	$area = area($tbl_essai['type'],$tbl_essai['dim_1'],$tbl_essai['dim_2'],$tbl_essai['dim_3']);
	
	
	
	

	/** Error reporting */
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	date_default_timezone_set('Europe/Paris');
	if (PHP_SAPI == 'cli')
		die('This example should only be run from a Web Browser');

	/** Include PHPExcel */
	require_once '../Excel/PHPExcel_1.8.0_doc/Classes/PHPExcel.php';


	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();
	$objReader = PHPExcel_IOFactory::createReader('Excel2007');


	If (isset($tbl_essai['type_essai']) AND $tbl_essai['type_essai']=="HCF")	{

		$objPHPExcel = $objReader->load("../Excel/templates/HCF-LCF CTRL EFFORT FT TestSuite.xlsx");


		
		$objPHPExcel->getActiveSheet()->setCellValue('B7', $identification);
		$objPHPExcel->getActiveSheet()->setCellValue('B8', $tbl_essai['dessin']);
		$objPHPExcel->getActiveSheet()->setCellValue('B9', $tbl_essai['material']);
		$objPHPExcel->getActiveSheet()->setCellValue('B13', $tbl_essai['machine']);
		$objPHPExcel->getActiveSheet()->setCellValue('B15', '40001');
		$objPHPExcel->getActiveSheet()->setCellValue('B16', $tbl_essai['enregistreur']);
		$objPHPExcel->getActiveSheet()->setCellValue('F13', $compresseur);
		$objPHPExcel->getActiveSheet()->setCellValue('F17', $ind_temp);
		$objPHPExcel->getActiveSheet()->setCellValue('B17', $tbl_essai['extensometre']);
		$objPHPExcel->getActiveSheet()->setCellValue('F15', $coil);
		$objPHPExcel->getActiveSheet()->setCellValue('F16', $four);

		$objPHPExcel->getActiveSheet()->setCellValue('B24', $tbl_essai['cartouche_load']);
		$objPHPExcel->getActiveSheet()->setCellValue('B23', $tbl_essai['cartouche_stroke']);
		$objPHPExcel->getActiveSheet()->setCellValue('B25', $tbl_essai['cartouche_strain']);

		$objPHPExcel->getActiveSheet()->setCellValue('B28', "6");

		
		$objPHPExcel->getActiveSheet()->setCellValue('D28', "-6");
		

		$objPHPExcel->getActiveSheet()->setCellValue('I7', $jobcomplet);
		$objPHPExcel->getActiveSheet()->setCellValue('I8', $tbl_essai['n_essai']);
		$objPHPExcel->getActiveSheet()->setCellValue('I9', $tbl_essai['n_fichier']);
		$objPHPExcel->getActiveSheet()->setCellValue('I10', $tbl_essai['date']);
		$objPHPExcel->getActiveSheet()->setCellValue('I11', $tbl_essai['operateur']);
		$objPHPExcel->getActiveSheet()->setCellValue('I12', $tbl_essai['controleur']);

		$objPHPExcel->getActiveSheet()->setCellValue('J17', $tbl_essai['operateur']);
		$objPHPExcel->getActiveSheet()->setCellValue('J18', $tbl_essai['controleur']);
		$objPHPExcel->getActiveSheet()->setCellValue('K20', $tbl_essai['c_temperature']);
		$objPHPExcel->getActiveSheet()->setCellValue('K22', $R);
		$objPHPExcel->getActiveSheet()->setCellValue('K23', $tbl_essai['c_frequence']);
		$objPHPExcel->getActiveSheet()->setCellValue('I22', $A);
		$objPHPExcel->getActiveSheet()->setCellValue('I23', $tbl_essai['c_waveform']);	
		
		if ($nb_dim==1)	{
			$objPHPExcel->getActiveSheet()->setCellValue('I24', $tbl_essai['dim_1']);
		}
		elseif ($nb_dim==2)	{
			$objPHPExcel->getActiveSheet()->setCellValue('I24', $tbl_essai['dim_1']);
			$objPHPExcel->getActiveSheet()->setCellValue('K24', $tbl_essai['dim_2']);
		}
		elseif ($nb_dim==3)	{
			$objPHPExcel->getActiveSheet()->setCellValue('I24', $tbl_essai['dim_1']);
			$objPHPExcel->getActiveSheet()->setCellValue('K24', $tbl_essai['dim_2']);
			$objPHPExcel->getActiveSheet()->setCellValue('K25', $tbl_essai['dim_3']);
		}
		else	{
			$objPHPExcel->getActiveSheet()->setCellValue('I24', "NA");
		}


		$objPHPExcel->getActiveSheet()->setCellValue('I25', $area);

		if ($tbl_essai['c_unite']=="MPa")	{
			$objPHPExcel->getActiveSheet()->setCellValue('I28', $MAX);
				$objPHPExcel->getActiveSheet()->setCellValue('I29', ($MAX+$MIN)/2);
				$objPHPExcel->getActiveSheet()->setCellValue('I30', ($MAX-$MIN)/2);
			$objPHPExcel->getActiveSheet()->setCellValue('I31', $MIN);	

			$objPHPExcel->getActiveSheet()->setCellValue('J28', $MAX*$area/1000);
				$objPHPExcel->getActiveSheet()->setCellValue('J29', ($MAX+$MIN)/2*$area/1000);
				$objPHPExcel->getActiveSheet()->setCellValue('J30', ($MAX-$MIN)/2*$area/1000);
			$objPHPExcel->getActiveSheet()->setCellValue('J31', $MIN*$area/1000);
			
			$limiteload=($MAX*$area/1000<10)?0.5:$MAX*$area/1000*5/100;
			$objPHPExcel->getActiveSheet()->setCellValue('B29', $MAX*$area/1000+$limiteload);
			$objPHPExcel->getActiveSheet()->setCellValue('D29', $MIN*$area/1000-$limiteload);	
		}
		Elseif ($tbl_essai['c_unite']=="kN")	{
			$objPHPExcel->getActiveSheet()->setCellValue('J28', $MAX);
				$objPHPExcel->getActiveSheet()->setCellValue('J29', ($MAX+$MIN)/2);
				$objPHPExcel->getActiveSheet()->setCellValue('J30', ($MAX-$MIN)/2);			
			$objPHPExcel->getActiveSheet()->setCellValue('J31', $MIN);
			
			$objPHPExcel->getActiveSheet()->setCellValue('B29', $MAX+$MAX*5/100);
			$objPHPExcel->getActiveSheet()->setCellValue('D29', $MIN-$MAX*5/100);			
		}
		Else	{
			$objPHPExcel->getActiveSheet()->setCellValue('J28', "ERREUR d'unité");			
		}
		
		$objPHPExcel->getActiveSheet()->setCellValue('K52', $STL);
		$objPHPExcel->getActiveSheet()->setCellValue('I52', $F_STL);

		
		$objPHPExcel->getActiveSheet()->setCellValue('J46', $tbl_essai['Cycle_min']);
		$objPHPExcel->getActiveSheet()->setCellValue('J49', $runout);
		
		
	}
	ElseIf (isset($tbl_essai['type_essai']) AND $tbl_essai['type_essai']=="HCF+Ext.")	{

		$objPHPExcel = $objReader->load("../Excel/templates/HCF-LCF CTRL EFFORT FT TestSuite.xlsx");


		
		$objPHPExcel->getActiveSheet()->setCellValue('B7', $identification);
		$objPHPExcel->getActiveSheet()->setCellValue('B8', $tbl_essai['dessin']);
		$objPHPExcel->getActiveSheet()->setCellValue('B9', $tbl_essai['material']);
		$objPHPExcel->getActiveSheet()->setCellValue('B13', $tbl_essai['machine']);
		$objPHPExcel->getActiveSheet()->setCellValue('B15', '40001');
		$objPHPExcel->getActiveSheet()->setCellValue('B16', $tbl_essai['enregistreur']);
		$objPHPExcel->getActiveSheet()->setCellValue('F13', $compresseur);
		$objPHPExcel->getActiveSheet()->setCellValue('F17', $ind_temp);
		$objPHPExcel->getActiveSheet()->setCellValue('B17', $tbl_essai['extensometre']);
		$objPHPExcel->getActiveSheet()->setCellValue('F15', $coil);
		$objPHPExcel->getActiveSheet()->setCellValue('F16', $four);

		$objPHPExcel->getActiveSheet()->setCellValue('B24', $tbl_essai['cartouche_load']);
		$objPHPExcel->getActiveSheet()->setCellValue('B23', $tbl_essai['cartouche_stroke']);
		$objPHPExcel->getActiveSheet()->setCellValue('B25', $tbl_essai['cartouche_strain']);

		$objPHPExcel->getActiveSheet()->setCellValue('B28', "6");

		
		$objPHPExcel->getActiveSheet()->setCellValue('D28', "-6");
		

		$objPHPExcel->getActiveSheet()->setCellValue('I7', $jobcomplet);
		$objPHPExcel->getActiveSheet()->setCellValue('I8', $tbl_essai['n_essai']);
		$objPHPExcel->getActiveSheet()->setCellValue('I9', $tbl_essai['n_fichier']);
		$objPHPExcel->getActiveSheet()->setCellValue('I10', $tbl_essai['date']);
		$objPHPExcel->getActiveSheet()->setCellValue('I11', $tbl_essai['operateur']);
		$objPHPExcel->getActiveSheet()->setCellValue('I12', $tbl_essai['controleur']);

		$objPHPExcel->getActiveSheet()->setCellValue('J17', $tbl_essai['operateur']);
		if ($tbl_essai['c_temperature']>=50)
			$objPHPExcel->getActiveSheet()->setCellValue('J18', $tbl_essai['controleur']);
		$objPHPExcel->getActiveSheet()->setCellValue('K20', $tbl_essai['c_temperature']);
		$objPHPExcel->getActiveSheet()->setCellValue('K22', $R);
		$objPHPExcel->getActiveSheet()->setCellValue('K23', $tbl_essai['c_frequence']);
		$objPHPExcel->getActiveSheet()->setCellValue('I22', $A);
		$objPHPExcel->getActiveSheet()->setCellValue('I23', $tbl_essai['c_waveform']);	
		
		if ($nb_dim==1)	{
			$objPHPExcel->getActiveSheet()->setCellValue('I24', $tbl_essai['dim_1']);
		}
		elseif ($nb_dim==2)	{
			$objPHPExcel->getActiveSheet()->setCellValue('I24', $tbl_essai['dim_1']);
			$objPHPExcel->getActiveSheet()->setCellValue('K24', $tbl_essai['dim_2']);
		}
		elseif ($nb_dim==3)	{
			$objPHPExcel->getActiveSheet()->setCellValue('I24', $tbl_essai['dim_1']);
			$objPHPExcel->getActiveSheet()->setCellValue('K24', $tbl_essai['dim_2']);
			$objPHPExcel->getActiveSheet()->setCellValue('K25', $tbl_essai['dim_3']);
		}
		else	{
			$objPHPExcel->getActiveSheet()->setCellValue('I24', "NA");
		}


		$objPHPExcel->getActiveSheet()->setCellValue('I25', $area);

		if ($tbl_essai['c_unite']=="MPa")	{
			$objPHPExcel->getActiveSheet()->setCellValue('I28', $MAX);
				$objPHPExcel->getActiveSheet()->setCellValue('I29', ($MAX+$MIN)/2);
				$objPHPExcel->getActiveSheet()->setCellValue('I30', ($MAX-$MIN)/2);
			$objPHPExcel->getActiveSheet()->setCellValue('I31', $MIN);	

			$objPHPExcel->getActiveSheet()->setCellValue('J28', $MAX*$area/1000);
				$objPHPExcel->getActiveSheet()->setCellValue('J29', ($MAX+$MIN)/2*$area/1000);
				$objPHPExcel->getActiveSheet()->setCellValue('J30', ($MAX-$MIN)/2*$area/1000);
			$objPHPExcel->getActiveSheet()->setCellValue('J31', $MIN*$area/1000);
			
			$objPHPExcel->getActiveSheet()->setCellValue('B29', $MAX*$area/1000+$MAX*$area/1000*5/100);
			$objPHPExcel->getActiveSheet()->setCellValue('D29', $MIN*$area/1000-$MAX*$area/1000*5/100);	
		}
		Elseif ($tbl_essai['c_unite']=="kN")	{
			$objPHPExcel->getActiveSheet()->setCellValue('J28', $MAX);
				$objPHPExcel->getActiveSheet()->setCellValue('J29', ($MAX+$MIN)/2);
				$objPHPExcel->getActiveSheet()->setCellValue('J30', ($MAX-$MIN)/2);			
			$objPHPExcel->getActiveSheet()->setCellValue('J31', $MIN);
			
			$objPHPExcel->getActiveSheet()->setCellValue('B29', $MAX+$MAX*5/100);
			$objPHPExcel->getActiveSheet()->setCellValue('D29', $MIN-$MAX*5/100);			
		}
		Else	{
			$objPHPExcel->getActiveSheet()->setCellValue('J28', "ERREUR d'unité");			
		}
		
		$objPHPExcel->getActiveSheet()->setCellValue('K52', $STL);
		$objPHPExcel->getActiveSheet()->setCellValue('I52', $F_STL);

		
		$objPHPExcel->getActiveSheet()->setCellValue('J46', $tbl_essai['Cycle_min']);
		$objPHPExcel->getActiveSheet()->setCellValue('J49', $runout);
		
		
	}
	ElseIf (isset($tbl_essai['type_essai']) AND $tbl_essai['type_essai']=="Dwell")	{

		$objPHPExcel = $objReader->load("../Excel/templates/HCF-LCF CTRL EFFORT FT TestSuite.xlsx");


		
		$objPHPExcel->getActiveSheet()->setCellValue('B7', $identification);
		$objPHPExcel->getActiveSheet()->setCellValue('B8', $tbl_essai['dessin']);
		$objPHPExcel->getActiveSheet()->setCellValue('B9', $tbl_essai['material']);
		$objPHPExcel->getActiveSheet()->setCellValue('B13', $tbl_essai['machine']);
		$objPHPExcel->getActiveSheet()->setCellValue('B15', '40001');
		$objPHPExcel->getActiveSheet()->setCellValue('B16', $tbl_essai['enregistreur']);
		$objPHPExcel->getActiveSheet()->setCellValue('F13', $compresseur);
		$objPHPExcel->getActiveSheet()->setCellValue('F17', $ind_temp);
		$objPHPExcel->getActiveSheet()->setCellValue('B17', $tbl_essai['extensometre']);
		$objPHPExcel->getActiveSheet()->setCellValue('F15', $coil);
		$objPHPExcel->getActiveSheet()->setCellValue('F16', $four);

		$objPHPExcel->getActiveSheet()->setCellValue('B24', $tbl_essai['cartouche_load']);
		$objPHPExcel->getActiveSheet()->setCellValue('B23', $tbl_essai['cartouche_stroke']);
		$objPHPExcel->getActiveSheet()->setCellValue('B25', $tbl_essai['cartouche_strain']);

		$objPHPExcel->getActiveSheet()->setCellValue('B28', "6");

		
		$objPHPExcel->getActiveSheet()->setCellValue('D28', "-6");
		

		$objPHPExcel->getActiveSheet()->setCellValue('I7', $jobcomplet);
		$objPHPExcel->getActiveSheet()->setCellValue('I8', $tbl_essai['n_essai']);
		$objPHPExcel->getActiveSheet()->setCellValue('I9', $tbl_essai['n_fichier']);
		$objPHPExcel->getActiveSheet()->setCellValue('I10', $tbl_essai['date']);
		$objPHPExcel->getActiveSheet()->setCellValue('I11', $tbl_essai['operateur']);
		$objPHPExcel->getActiveSheet()->setCellValue('I12', $tbl_essai['controleur']);

		$objPHPExcel->getActiveSheet()->setCellValue('J17', $tbl_essai['operateur']);
		if ($tbl_essai['c_temperature']>=50)
			$objPHPExcel->getActiveSheet()->setCellValue('J18', $tbl_essai['controleur']);
		$objPHPExcel->getActiveSheet()->setCellValue('K20', $tbl_essai['c_temperature']);
		$objPHPExcel->getActiveSheet()->setCellValue('K22', $R);
		$objPHPExcel->getActiveSheet()->setCellValue('K23', $tbl_essai['c_frequence']);
		$objPHPExcel->getActiveSheet()->setCellValue('I22', $A);
		$objPHPExcel->getActiveSheet()->setCellValue('I23', $tbl_essai['c_waveform']);	
		
		if ($nb_dim==1)	{
			$objPHPExcel->getActiveSheet()->setCellValue('I24', $tbl_essai['dim_1']);
		}
		elseif ($nb_dim==2)	{
			$objPHPExcel->getActiveSheet()->setCellValue('I24', $tbl_essai['dim_1']);
			$objPHPExcel->getActiveSheet()->setCellValue('K24', $tbl_essai['dim_2']);
		}
		elseif ($nb_dim==3)	{
			$objPHPExcel->getActiveSheet()->setCellValue('I24', $tbl_essai['dim_1']);
			$objPHPExcel->getActiveSheet()->setCellValue('K24', $tbl_essai['dim_2']);
			$objPHPExcel->getActiveSheet()->setCellValue('K25', $tbl_essai['dim_3']);
		}
		else	{
			$objPHPExcel->getActiveSheet()->setCellValue('I24', "NA");
		}


		$objPHPExcel->getActiveSheet()->setCellValue('I25', $area);

		if ($tbl_essai['c_unite']=="MPa")	{
			$objPHPExcel->getActiveSheet()->setCellValue('I28', $MAX);
				$objPHPExcel->getActiveSheet()->setCellValue('I29', ($MAX+$MIN)/2);
				$objPHPExcel->getActiveSheet()->setCellValue('I30', ($MAX-$MIN)/2);
			$objPHPExcel->getActiveSheet()->setCellValue('I31', $MIN);	

			$objPHPExcel->getActiveSheet()->setCellValue('J28', $MAX*$area/1000);
				$objPHPExcel->getActiveSheet()->setCellValue('J29', ($MAX+$MIN)/2*$area/1000);
				$objPHPExcel->getActiveSheet()->setCellValue('J30', ($MAX-$MIN)/2*$area/1000);
			$objPHPExcel->getActiveSheet()->setCellValue('J31', $MIN*$area/1000);
			
			$objPHPExcel->getActiveSheet()->setCellValue('B29', $MAX*$area/1000+$MAX*$area/1000*5/100);
			$objPHPExcel->getActiveSheet()->setCellValue('D29', $MIN*$area/1000-$MAX*$area/1000*5/100);	
		}
		Elseif ($tbl_essai['c_unite']=="kN")	{
			$objPHPExcel->getActiveSheet()->setCellValue('J28', $MAX);
				$objPHPExcel->getActiveSheet()->setCellValue('J29', ($MAX+$MIN)/2);
				$objPHPExcel->getActiveSheet()->setCellValue('J30', ($MAX-$MIN)/2);			
			$objPHPExcel->getActiveSheet()->setCellValue('J31', $MIN);
			
			$objPHPExcel->getActiveSheet()->setCellValue('B29', $MAX+$MAX*5/100);
			$objPHPExcel->getActiveSheet()->setCellValue('D29', $MIN-$MAX*5/100);			
		}
		Else	{
			$objPHPExcel->getActiveSheet()->setCellValue('J28', "ERREUR d'unité");			
		}
		
		$objPHPExcel->getActiveSheet()->setCellValue('K52', $STL);
		$objPHPExcel->getActiveSheet()->setCellValue('I52', $F_STL);

		
		$objPHPExcel->getActiveSheet()->setCellValue('J46', $tbl_essai['Cycle_min']);
		$objPHPExcel->getActiveSheet()->setCellValue('J49', $runout);
		
		
	}
	ElseIf (isset($tbl_essai['type_essai']) AND $tbl_essai['type_essai']=="LCF")	{

		$objPHPExcel = $objReader->load("../Excel/templates/LCF CTRL DEF FT TestSuite.xlsx");


		
		$objPHPExcel->getActiveSheet()->setCellValue('B7', $identification);
		$objPHPExcel->getActiveSheet()->setCellValue('B8', $tbl_essai['dessin']);
		$objPHPExcel->getActiveSheet()->setCellValue('B9', $tbl_essai['material']);
		$objPHPExcel->getActiveSheet()->setCellValue('B14', $tbl_essai['machine']);
		$objPHPExcel->getActiveSheet()->setCellValue('B15', '40001');
		$objPHPExcel->getActiveSheet()->setCellValue('B16', $tbl_essai['enregistreur']);
		$objPHPExcel->getActiveSheet()->setCellValue('F14', $compresseur);
		$objPHPExcel->getActiveSheet()->setCellValue('F17', $ind_temp);
		$objPHPExcel->getActiveSheet()->setCellValue('B17', $tbl_essai['extensometre']);
		$objPHPExcel->getActiveSheet()->setCellValue('F15', $coil);
		$objPHPExcel->getActiveSheet()->setCellValue('F16', $four);

		$objPHPExcel->getActiveSheet()->setCellValue('B24', $tbl_essai['cartouche_load']);
		$objPHPExcel->getActiveSheet()->setCellValue('B23', $tbl_essai['cartouche_stroke']);
		$objPHPExcel->getActiveSheet()->setCellValue('B25', $tbl_essai['cartouche_strain']);

		$objPHPExcel->getActiveSheet()->setCellValue('B28', 3);
		$objPHPExcel->getActiveSheet()->setCellValue('B29', "");
		$objPHPExcel->getActiveSheet()->setCellValue('B30', $MAX+0.15);
		
		$objPHPExcel->getActiveSheet()->setCellValue('D28', -3);
		$objPHPExcel->getActiveSheet()->setCellValue('D29', "");
		$objPHPExcel->getActiveSheet()->setCellValue('D30', $MIN-0.15);		

		$objPHPExcel->getActiveSheet()->setCellValue('I7', $jobcomplet);
		$objPHPExcel->getActiveSheet()->setCellValue('I8', $tbl_essai['n_essai']);
		$objPHPExcel->getActiveSheet()->setCellValue('I9', $tbl_essai['n_fichier']);
		$objPHPExcel->getActiveSheet()->setCellValue('I10', $tbl_essai['date']);
		$objPHPExcel->getActiveSheet()->setCellValue('I11', $tbl_essai['operateur']);
		$objPHPExcel->getActiveSheet()->setCellValue('I12', $tbl_essai['controleur']);

		$objPHPExcel->getActiveSheet()->setCellValue('J16', $tbl_essai['operateur']);
		if ($tbl_essai['c_temperature']>=50)
			$objPHPExcel->getActiveSheet()->setCellValue('J17', $tbl_essai['controleur']);
		$objPHPExcel->getActiveSheet()->setCellValue('K18', $tbl_essai['c_temperature']);
		$objPHPExcel->getActiveSheet()->setCellValue('K21', $R);
		$objPHPExcel->getActiveSheet()->setCellValue('K22', $tbl_essai['c_frequence']);
		$objPHPExcel->getActiveSheet()->setCellValue('I21', $A);
		$objPHPExcel->getActiveSheet()->setCellValue('I22', $tbl_essai['c_waveform']);	
		
		if ($nb_dim==1)	{
			$objPHPExcel->getActiveSheet()->setCellValue('J24', $tbl_essai['dim_1']);
		}
		elseif ($nb_dim==2)	{
			$objPHPExcel->getActiveSheet()->setCellValue('I24', $tbl_essai['dim_1']);
			$objPHPExcel->getActiveSheet()->setCellValue('J24', $tbl_essai['dim_2']);
		}
		elseif ($nb_dim==3)	{
			$objPHPExcel->getActiveSheet()->setCellValue('I24', $tbl_essai['dim_1']);
			$objPHPExcel->getActiveSheet()->setCellValue('J24', $tbl_essai['dim_2']);
			$objPHPExcel->getActiveSheet()->setCellValue('I23', $tbl_essai['dim_3']);
		}
		else	{
			$objPHPExcel->getActiveSheet()->setCellValue('J24', "NA");
		}


		$objPHPExcel->getActiveSheet()->setCellValue('J25', $area);
		$objPHPExcel->getActiveSheet()->setCellValue('J26', $tbl_essai['Lo']);
		
		$objPHPExcel->getActiveSheet()->setCellValue('J29', $MAX-$MIN);
		$objPHPExcel->getActiveSheet()->setCellValue('J30', $MAX);
		$objPHPExcel->getActiveSheet()->setCellValue('J31', $MIN);
		
		$objPHPExcel->getActiveSheet()->setCellValue('B45', $STL);
		$objPHPExcel->getActiveSheet()->setCellValue('B46', $F_STL);
		//$objPHPExcel->getActiveSheet()->setCellValue('J47', $tbl_essai['c_temperature']);
		
		$objPHPExcel->getActiveSheet()->setCellValue('J56', $tbl_essai['Cycle_min']);
		$objPHPExcel->getActiveSheet()->setCellValue('J59', $runout);
		
		
	}
	ElseIf (isset($tbl_essai['type_essai']) AND $tbl_essai['type_essai']=="Pre-Straining")	{

		$objPHPExcel = $objReader->load("../Excel/templates/FT PS.xlsx");


		
		$objPHPExcel->getActiveSheet()->setCellValue('B7', $identification);
		$objPHPExcel->getActiveSheet()->setCellValue('B8', $tbl_essai['dessin']);
		$objPHPExcel->getActiveSheet()->setCellValue('B9', $tbl_essai['material']);
		$objPHPExcel->getActiveSheet()->setCellValue('B14', $tbl_essai['machine']);
		$objPHPExcel->getActiveSheet()->setCellValue('B15', '40001');
		$objPHPExcel->getActiveSheet()->setCellValue('B16', $tbl_essai['enregistreur']);
		$objPHPExcel->getActiveSheet()->setCellValue('F14', $compresseur);
		$objPHPExcel->getActiveSheet()->setCellValue('F17', $ind_temp);
		$objPHPExcel->getActiveSheet()->setCellValue('B17', $tbl_essai['extensometre']);
		$objPHPExcel->getActiveSheet()->setCellValue('F15', $coil);
		$objPHPExcel->getActiveSheet()->setCellValue('F16', $four);

		$objPHPExcel->getActiveSheet()->setCellValue('B24', $tbl_essai['cartouche_load']);
		$objPHPExcel->getActiveSheet()->setCellValue('B23', $tbl_essai['cartouche_stroke']);
		$objPHPExcel->getActiveSheet()->setCellValue('B25', $tbl_essai['cartouche_strain']);

		$objPHPExcel->getActiveSheet()->setCellValue('B28', "");
		$objPHPExcel->getActiveSheet()->setCellValue('B29', "");
		$objPHPExcel->getActiveSheet()->setCellValue('B30', $MAX+0.15);
		
		$objPHPExcel->getActiveSheet()->setCellValue('D28', "");
		$objPHPExcel->getActiveSheet()->setCellValue('D29', "");
		$objPHPExcel->getActiveSheet()->setCellValue('D30', $MIN-0.15);		

		$objPHPExcel->getActiveSheet()->setCellValue('I7', $jobcomplet);
		$objPHPExcel->getActiveSheet()->setCellValue('I8', $tbl_essai['n_essai']);
		$objPHPExcel->getActiveSheet()->setCellValue('I9', $tbl_essai['n_fichier']);
		$objPHPExcel->getActiveSheet()->setCellValue('I10', $tbl_essai['date']);
		$objPHPExcel->getActiveSheet()->setCellValue('I11', $tbl_essai['operateur']);
		$objPHPExcel->getActiveSheet()->setCellValue('I12', $tbl_essai['controleur']);

		$objPHPExcel->getActiveSheet()->setCellValue('J16', $tbl_essai['operateur']);
		if ($tbl_essai['c_temperature']>=50)
			$objPHPExcel->getActiveSheet()->setCellValue('J17', $tbl_essai['controleur']);
		$objPHPExcel->getActiveSheet()->setCellValue('K18', $tbl_essai['c_temperature']);
		$objPHPExcel->getActiveSheet()->setCellValue('K21', $R);
		$objPHPExcel->getActiveSheet()->setCellValue('K22', $tbl_essai['c_frequence']);
		$objPHPExcel->getActiveSheet()->setCellValue('I21', $A);
		$objPHPExcel->getActiveSheet()->setCellValue('I22', $tbl_essai['c_waveform']);	
		
		if ($nb_dim==1)	{
			$objPHPExcel->getActiveSheet()->setCellValue('J24', $tbl_essai['dim_1']);
		}
		elseif ($nb_dim==2)	{
			$objPHPExcel->getActiveSheet()->setCellValue('I24', $tbl_essai['dim_1']);
			$objPHPExcel->getActiveSheet()->setCellValue('J24', $tbl_essai['dim_2']);
		}
		elseif ($nb_dim==3)	{
			$objPHPExcel->getActiveSheet()->setCellValue('I24', $tbl_essai['dim_1']);
			$objPHPExcel->getActiveSheet()->setCellValue('J24', $tbl_essai['dim_2']);
			$objPHPExcel->getActiveSheet()->setCellValue('I23', $tbl_essai['dim_3']);
		}
		else	{
			$objPHPExcel->getActiveSheet()->setCellValue('J24', "NA");
		}


		$objPHPExcel->getActiveSheet()->setCellValue('J25', $area);
		$objPHPExcel->getActiveSheet()->setCellValue('J26', $tbl_essai['Lo']);
		
		$objPHPExcel->getActiveSheet()->setCellValue('J29', $MAX-$MIN);
		$objPHPExcel->getActiveSheet()->setCellValue('J30', $MAX);
		$objPHPExcel->getActiveSheet()->setCellValue('J31', $MIN);
		
		$objPHPExcel->getActiveSheet()->setCellValue('B45', $STL);
		$objPHPExcel->getActiveSheet()->setCellValue('B46', $F_STL);
		//$objPHPExcel->getActiveSheet()->setCellValue('J47', $tbl_essai['c_temperature']);
		
		$objPHPExcel->getActiveSheet()->setCellValue('J56', $tbl_essai['Cycle_min']);
		$objPHPExcel->getActiveSheet()->setCellValue('J59', $runout);
		
		
	}

	
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('../Excel/'.$tbl_essai['n_fichier'].'.xlsx');

	
	// Redirect output to a client’s web browser (Excel2007)
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="FT-'.$tbl_essai['n_fichier'].'.xlsx"');
	header('Cache-Control: max-age=0');
	// If you're serving to IE 9, then the following may be needed
	header('Cache-Control: max-age=1');

	// If you're serving to IE over SSL, then the following may be needed
	header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
	header ('Pragma: public'); // HTTP/1.0

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;

}
?>
<div id="choixessai">Choix de l'essai			<!--choix du poste(machine)-->
	<form method="post" name="choixessai" onchange="submit()">
		<INPUT type=text name="n_fichier">
	</form>
</div>