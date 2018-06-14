<#1>
<?php
	// Add JSME Question Type
	$res = $ilDB->queryF("SELECT * FROM qpl_qst_type WHERE type_tag = %s",
		array('text'),
		array('assJSMEQuestion')
	);
	if ($res->numRows() == 0)
	{
		$res = $ilDB->query("SELECT MAX(question_type_id) maxid FROM qpl_qst_type");
		$data = $ilDB->fetchAssoc($res);
		$max = $data["maxid"] + 1;

		$affectedRows = $ilDB->manipulateF("INSERT INTO qpl_qst_type (question_type_id, type_tag, plugin) VALUES (%s, %s, %s)", 
			array("integer", "text", "integer"),
			array($max, 'assJSMEQuestion', 1)
		);
	}
?>
<#2>
<?php
	//Define JSME data
	$fields = array(
			'question_fi'	=> array('type' => 'integer', 'length' => 4, 'notnull' => true ),
			'option_string' => array('type' => 'text', 'length' => 200, 'fixed' => false, 'notnull' => false ),
			'solution'      => array('type' => 'clob', 'notnull' => false )
	);
	$ilDB->createTable("il_qpl_qst_jsme_data", $fields);
	$ilDB->addPrimaryKey("il_qpl_qst_jsme_data", array("question_fi"));	
?>
<#3>
<?php
	//Add SMILES to table for autoevaluation
    if(!$ilDB->tableColumnExists('il_qpl_qst_jsme_data', 'smiles'))
    {
        $ilDB->addTableColumn('il_qpl_qst_jsme_data', 'smiles', array(
                'type' => 'text',
                'length' => 200,
                'notnull' => false,
            )
        );
    }
?>
<#4>
<?php
	//Add SVG to table for presentation in PDF and for manual correction
    if(!$ilDB->tableColumnExists('il_qpl_qst_jsme_data', 'svg'))
    {
        $ilDB->addTableColumn('il_qpl_qst_jsme_data', 'svg', array(
                'type' => 'clob',
                'notnull' => false,
            )
        );
    }
?>
<#5>
<?php
	//Enlarge storage for options and smilies
    if($ilDB->tableColumnExists('il_qpl_qst_jsme_data', 'option_string'))
    {
    	$ilDB->query('ALTER TABLE il_qpl_qst_jsme_data MODIFY option_string VARCHAR(1000)');
    }
    if($ilDB->tableColumnExists('il_qpl_qst_jsme_data', 'smiles'))
    {
    	$ilDB->query('ALTER TABLE il_qpl_qst_jsme_data MODIFY smiles VARCHAR(1000)');
    }
?>
<#6>
<?php
	//Set default values for existing JSME-Questions, to keep them "exam-safe"
	$ilDB->manipulate('UPDATE il_qpl_qst_jsme_data SET option_string = "nosearchinchiKey nopaste" WHERE option_string = ""');
?>