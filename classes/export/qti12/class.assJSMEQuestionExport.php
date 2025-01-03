<?php

include_once "./Modules/TestQuestionPool/classes/export/qti12/class.assQuestionExport.php";

/**
* assJSMEQuestionExport export
*
* @author Yves Annanias <yves.annanias@llz.uni-halle.de>
* @author Christoph Jobst <cjobst@wifa.uni-leipzig.de>
* @version	$Id:  $
* @ingroup ModulesTestQuestionPool
*/
class assJSMEQuestionExport extends assQuestionExport
{
	/**
	* Returns a QTI xml representation of the question
	*
	* @return string The QTI xml representation of the question
	* @access public
	*/
    function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false): string
    {
		global $ilias;
		
		include_once("./Services/Xml/classes/class.ilXmlWriter.php");
		$a_xml_writer = new ilXmlWriter;
		// set xml header
		$a_xml_writer->xmlHeader();
		$a_xml_writer->xmlStartTag("questestinterop");
		$attrs = array(
			"ident" => "il_".IL_INST_ID."_qst_".$this->object->getId(),
			"title" => $this->object->getTitle(),
			"maxattempts" => $this->object->getNrOfTries()
		);
		$a_xml_writer->xmlStartTag("item", $attrs);
		// add question description
		$a_xml_writer->xmlElement("qticomment", NULL, $this->object->getComment());
		
		// add estimated working time
		//$workingtime = $this->object->getEstimatedWorkingTime();
		//$duration = sprintf("P0Y0M0DT%dH%dM%dS", $workingtime["h"], $workingtime["m"], $workingtime["s"]);
		//$a_xml_writer->xmlElement("duration", NULL, $duration);
		
		// add ILIAS specific metadata
		$a_xml_writer->xmlStartTag("itemmetadata");
		$a_xml_writer->xmlStartTag("qtimetadata");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "ILIAS_VERSION");
		$a_xml_writer->xmlElement("fieldentry", NULL, $ilias->getSetting("ilias_version"));
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "QUESTIONTYPE");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getQuestionType());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "AUTHOR");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getAuthor());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "POINTS");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getPoints());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		//Question specific fields
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "option_string");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getOptionString());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		//
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "sample_solution");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getSampleSolution());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "smiles_solution");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getSmilesSolution());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "svg");
		$a_xml_writer->xmlElement("fieldentry", NULL, base64_encode($this->object->getSvg()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		//End Question specific fields
		
		// additional content editing information
		$this->addAdditionalContentEditingModeInformation($a_xml_writer);
		$this->addGeneralMetadata($a_xml_writer);

		$a_xml_writer->xmlEndTag("qtimetadata");
		$a_xml_writer->xmlEndTag("itemmetadata");

		// PART I: qti presentation
		$attrs = array(
			"label" => $this->object->getTitle()
		);
		$a_xml_writer->xmlStartTag("presentation", $attrs);
		// add flow to presentation
		$a_xml_writer->xmlStartTag("flow");
		// add material with question text to presentation
		$this->addQTIMaterial($a_xml_writer, $this->object->getQuestion());

		$a_xml_writer->xmlEndTag("flow");
		$a_xml_writer->xmlEndTag("presentation");


		// PART III: qti itemfeedback
		$feedback_allcorrect = $this->object->feedbackOBJ->getGenericFeedbackExportPresentation(
			$this->object->getId(), true
		);

		$feedback_onenotcorrect = $this->object->feedbackOBJ->getGenericFeedbackExportPresentation(
			$this->object->getId(), false
		);

		$attrs = array(
			"ident" => "Correct",
			"view" => "All"
		);
		$a_xml_writer->xmlStartTag("itemfeedback", $attrs);
		// qti flow_mat
		$a_xml_writer->xmlStartTag("flow_mat");
		$a_xml_writer->xmlStartTag("material");
		$a_xml_writer->xmlElement("mattext");
		$a_xml_writer->xmlEndTag("material");
		$a_xml_writer->xmlEndTag("flow_mat");
		$a_xml_writer->xmlEndTag("itemfeedback");
		if (strlen($feedback_allcorrect))
		{
			$attrs = array(
				"ident" => "response_allcorrect",
				"view" => "All"
			);
			$a_xml_writer->xmlStartTag("itemfeedback", $attrs);
			// qti flow_mat
			$a_xml_writer->xmlStartTag("flow_mat");
			$this->addQTIMaterial($a_xml_writer, $feedback_allcorrect);
			$a_xml_writer->xmlEndTag("flow_mat");
			$a_xml_writer->xmlEndTag("itemfeedback");
		}
		if (strlen($feedback_onenotcorrect))
		{
			$attrs = array(
				"ident" => "response_onenotcorrect",
				"view" => "All"
			);
			$a_xml_writer->xmlStartTag("itemfeedback", $attrs);
			// qti flow_mat
			$a_xml_writer->xmlStartTag("flow_mat");
			$this->addQTIMaterial($a_xml_writer, $feedback_onenotcorrect);
			$a_xml_writer->xmlEndTag("flow_mat");
			$a_xml_writer->xmlEndTag("itemfeedback");
		}

		$a_xml_writer = $this->addSolutionHints($a_xml_writer);
				
		$a_xml_writer->xmlEndTag("item");
		$a_xml_writer->xmlEndTag("questestinterop");

		$xml = $a_xml_writer->xmlDumpMem(FALSE);
		if (!$a_include_header)
		{
			$pos = strpos($xml, "?>");
			$xml = substr($xml, $pos + 2);
		}
		return $xml;
	}
}

?>