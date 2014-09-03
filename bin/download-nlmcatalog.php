#!/usr/bin/php -q
<?php

require dirname(__FILE__) . '/../vendor/autoload.php';

use \UmnLib\Core\NcbiEUtilsClient\Client;
use \UmnLib\Core\File\Set\DateSequence;

$env = getPhpUnitEnvVars(file_get_contents(dirname(__FILE__) . '/../phpunit.xml'));
$directory = (array_key_exists('1', $argv) && is_dir($argv[1])) ? $argv[1] : '.';

$searchTerms = generateSearchTerms();

$fileSet = new DateSequence(array(
  'directory' => $directory,
  'suffix' => '.xml',
));

$c = new Client(array(
  'email' => $env['NCBI_USER_EMAIL'],
  'db'    => 'nlmcatalog',
  'tool'  => $env['NCBI_USER_TOOL'],
  'searchTerms' => $searchTerms,
  'recordType' => 'citation',
  'fileSet' => $fileSet,
));

$result = $c->extract();

function getPhpUnitEnvVars($phpUnitXml)
{
  $xml = new \SimpleXMLElement($phpUnitXml);
  $env = array();
  foreach ($xml->php->env as $envElem) {
    unset($name, $value);
    foreach($envElem->attributes() as $k => $v) {
      $stringv = (string) $v;
      if ($k == 'name') {
        $name = $stringv;
      } else if ($k == 'value') {
        $value = $stringv;
      }
    }
    $env[$name] = $value;
  }
  return $env;
}

function generateSearchTerms() {
  $terms = array
    (
      'Abortion' => 'abortion, criminal[mh] OR (Abortion, Induced[mh] AND (classification[sh] OR economics[sh] OR education[sh] OR history[sh] OR jurisprudence[sh] OR mortality[sh] OR standards[sh] OR "statistics and numerical data"[sh] OR trends[sh])) OR abortion applicants[mh] OR (abortion and Attitude of Health Personnel[mh])',
      'Animals' => 'animal rights OR animal experimentation OR animals, genetically modified[majr] OR animal welfare[mh] OR vivisection[mh] OR (laboratory animals[mh:noexp] AND ("research and development" OR experimentation OR laboratory research OR welfare))',
      'Bias & Prejudice' => 'prejudice[majr] OR publication bias',
      'Children' => 'legal guardians[majr] OR parental consent[mh] OR parental notification OR paternalism OR child advocacy[mh] OR ((Child Welfare[mh:noexp]) AND (economics or jurisprudence OR standards OR statistics OR trends))',
      'Cloning' => '(cloning, organism[mh] AND (research OR standards OR statistics OR adverse OR history OR economics OR jurisprudence OR trends))',
      'Death & Dying' => 'advance directives OR assisted suicide OR (brain death[majr] AND (attitude to death[mh] OR diagnosis)) OR capital punishment[majr] OR (cryopreservation AND (economics OR standards OR effects OR trends OR data)) OR euthanasia[mh] OR euthanasia[ti] OR suicide, assisted[mh] OR life support care[majr] OR right to die OR resuscitation orders OR terminal care[majr]',
      'Disabled or Mentally Ill' => '(insurance, psychiatric[majr] AND (parity OR accessibility OR jurisprudence) OR (sterilization, reproductive[mh] AND (mental retardation OR disabled persons)) OR commitment of mentally ill[majr] OR (disabled persons[mh:noexp] AND disease transmission) OR mental competency[mh]',
      'Ethics' => 'commodification[mh] OR eugenics OR fraud[mh] OR freedom[majr] OR whistleblowing',
      'Genetics' => '(gene therapy AND government agencies) OR genetic discrimination OR genetic enhancement[majr] OR genetic privacy OR (genetic screening[mh] AND (attitude OR discrimination OR insurance OR public opinion OR mandatory reporting OR health policy)) OR (human genome project[mh] AND (economics OR ethics OR jurisprudence))',
      'Genocide' => 'holocaust[mh] ',
      'Health Care' => 'health care disparities OR health care rationing[majr] OR health facilities[majr] OR healthcare disparities OR national socialism OR (health services accessibility[mh:noexp] AND (news[pt] OR developing countries OR freedom OR socioeconomic status OR jurisprudence)',
      'Human Rights' => 'human rights[majr] OR human rights abuses OR (consumer advocacy[mh] AND (quality of life[mh] OR legislation OR health promotion))',
      'Industry Ethics' => '(drug industry[majr] AND (health personnel OR physician\'s role OR health occupations[mh:noexp])) OR (gift giving AND (industry OR interprofessional relations OR professional-patient relations)) OR insurance selection bias[mh]',
      'Legislation & Jurisprudence' => 'mandatory programs[majr] OR ((government regulation OR jurisprudence[mh:noexp] OR legislation as topic[mh] OR health policy[mh:noexp] OR organizational policy[mh] OR policy making[mh] OR public policy[mh:noexp] OR politics[mh:noexp] OR guidelines as topic[mh:noexp] OR guideline[pt] OR legal cases[pt] OR legislation[pt] OR lj[sh]) AND humans AND (abortion, induced[majr:noexp] OR abortion, legal[majr] OR abortion, therapeutic[majr] OR  advance care planning[majr] OR brain death[majr] OR cesarean section[mh] OR cloning, organism[mh] OR contact tracing[mh] OR DNA[majr] OR embryo research[mh] OR embryo transfer[majr] OR gene therapy[majr] OR genes[majr] OR genetic counseling[majr] OR  genetic research[majr:noexp] OR genetic screening[majr] OR genetics, medical[majr:noexp] OR genome, human[majr] OR human experimentation OR human genome project[mh] OR intensive care units, neonatal[mh] OR life[ti] OR life support care[majr] OR living donors[majr] OR (patents as topic[majr] AND (base sequence[majr] OR cell line[majr] OR chimera[majr])) OR patient transfer[majr] OR prenatal diagnosis[majr] OR professional-patient relations[majr] OR reproductive techniques[majr:noexp] OR reproductive techniques, assisted[majr] OR sex determination analysis OR sex preselection[mh] OR stem cells[majr] OR terminally ill[majr] OR tissue and organ procurement[majr] OR tissue donors[majr:noexp] OR transplantation[majr])) OR supreme court decisions',
      'Life' => 'beginning of human life[mh] OR value of life[mh] OR wrongful life',
      'Patents' => '((patents as topic[mh] OR intellectual property[mh]) AND (genome OR biomedical OR human body OR genetically modified))',
      'Patient Rights & Privacy' => 'confidentiality[majr] OR consent forms[ti] OR deception[mh] OR disclosure[majr:noexp] OR duty to warn[mh] OR civil rights[majr] OR informed consent[majr] OR informed consent[ti] OR patient rights[majr] OR patient rights[mh] OR presumed consent OR patient self determination act OR proxy[mh] OR treatment refusal[majr] OR (patient participation[majr] AND (decision making[mh] OR quality of life[mh] OR quality assurance[mh] OR truth disclosure[mh]))',
      'Philosophical' => '((altruism[mh] OR philosophy[mh:noexp] OR philosophy, dental[mh:noexp] OR philosophy, medical[mh:noexp] OR philosophy, nursing[mh:noexp]) AND (abortion, induced[majr:noexp] OR abortion, legal[majr] OR abortion, therapeutic[majr] OR  advance care planning[majr] OR brain death[majr] OR cesarean section[mh] OR cloning, organism[mh] OR contact tracing[mh] OR DNA[majr] OR embryo research[mh] OR embryo transfer[majr] OR gene therapy[majr] OR genes[majr] OR genetic counseling[majr] OR  genetic research[majr:noexp] OR genetic screening[majr] OR genetics, medical[majr:noexp] OR genome, human[majr] OR human experimentation OR human genome project[mh] OR intensive care units, neonatal[mh] OR life[ti] OR life support care[majr] OR living donors[majr] OR (patents as topic[majr] AND (base sequence[majr] OR cell line[majr] OR chimera[majr])) OR patient transfer[majr] OR prenatal diagnosis[majr] OR professional-patient relations[majr] OR reproductive techniques[majr:noexp] OR reproductive techniques, assisted[majr] OR sex determination analysis OR sex preselection[mh] OR stem cells[majr] OR terminally ill[majr] OR tissue and organ procurement[majr] OR tissue donors[majr:noexp] OR transplantation[majr]))',
      'Public Health' => 'public health[mh:noexp] OR refusal to participate[mh] OR social control, formal OR social values[majr]',
      'Quarantine' => '(quarantine[majr] NOT veterinary NOT animals[mh:noexp])',
      'Religion' => '((buddhism OR christianity OR confucianism OR hinduism OR islam OR judaism OR religion[mh:noexp] OR religion and medicine[mh] OR theology[mh]) AND humans AND (abortion, induced[majr:noexp] OR abortion, legal[majr] OR abortion, therapeutic[majr] OR  advance care planning[majr] OR brain death[majr] OR cesarean section[mh] OR cloning, organism[mh] OR contact tracing[mh] OR DNA[majr] OR embryo research[mh] OR embryo transfer[majr] OR gene therapy[majr] OR genes[majr] OR genetic counseling[majr] OR  genetic research[majr:noexp] OR genetic screening[majr] OR genetics, medical[majr:noexp] OR genome, human[majr] OR human experimentation OR human genome project[mh] OR intensive care units, neonatal[mh] OR life[ti] OR life support care[majr] OR living donors[majr] OR (patents as topic[majr] AND (base sequence[majr] OR cell line[majr] OR chimera[majr])) OR patient transfer[majr] OR prenatal diagnosis[majr] OR professional-patient relations[majr] OR reproductive techniques[majr:noexp] OR reproductive techniques, assisted[majr] OR sex determination analysis OR sex preselection[mh] OR stem cells[majr] OR terminally ill[majr] OR tissue and organ procurement[majr] OR tissue donors[majr:noexp] OR transplantation[majr]))',
      'Reproduction' => 'sterilization, involuntary[mh] OR fertilization in vitro OR (oocyte donation[majr] AND humans AND (biomedical research OR confidentiality OR reproductive techniques)) OR posthumous conception OR personhood[ti] OR reproductive rights OR surrogate mothers[mh] OR (preimplantation diagnosis[mh] NOT methods) OR ((reproductive techniques[mh:noexp] OR reproductive techniques, assisted) AND (adverse effects OR economics OR ethics OR history OR jurisprudence OR standards OR statistics OR trends)) OR (sex determination analysis[mh] AND (adverse effects OR drug effects OR economics OR ethics OR history OR radiation effects OR standards OR statistics OR trends)) OR (sex preselection[mh] NOT veterinary)',
      'Research' => 'biomedical enhancement[mh:noexp] OR (biomedical research/trends OR biomedical research/legislation[Mh] OR biomedical research/standards[mh]) OR embryo disposition[mh] OR embryo research OR fetal research[mh] OR human experimentation[majr] OR scientific integrity review OR united states office of research integrity OR research embryo creation OR embryo research[Mh] OR (embryo research AND (economics OR ethics OR jurisprudence)) OR anonymous testing[majr] OR clinical trials as topic/lj[majr] OR clinical trials data monitoring committees[mh]',
      'Stem Cells' => 'stem cells[mh] AND (legislation OR standards OR statistics OR trends)',
      'Tissue & Organ Donation' => 'directed tissue donation OR ((Tissue and Organ Procurement OR Tissue Donors) AND (economics OR "legislation and jurisprudence" OR standards OR attitude OR commerce[mh] or decision making[mh] OR gift giving[mh]OR motivation[mh] OR public opinion[mh])) OR (malpractic[majr] OR (malpractice[mh] AND (economics OR jurisprudence OR prevention OR standards OR statistics OR trends)) AND (health care reform[mh] OR truth disclosure OR supreme court decisions))',
      'Treatment' => 'dehumanization[mh] OR duty to recontact OR refusal to treat OR medical futility OR truth disclosure[majr] OR withholding treatment[majr] OR quackery OR uncompensated care',
      'Vaccines' => 'Vaccines[mh] AND (("adverse effects"[sh] AND cost-benefit analysis[mh]) OR complications[sh] OR  lj[sh] OR poisoning[sh])',
      'War, Terrorism & Disasters' => 'biological warfare[majr] OR prisoners[majr] OR prisoners[mh] OR terrorism[mh] OR Torture[majr] OR (((war[mh] AND politics) OR war crimes) AND (health personnel[mh] OR physician\'s role OR nurse\'s role OR health occupations[majr:noexp])) OR (terrorism[mh] AND (health personnel OR public health OR disaster planning OR surveillance OR research[majr] OR civil rights OR coercion OR freedom OR mandatory programs[mh:noexp] OR voluntary programs[majr])) OR (Disaster Planning[mh] AND (jurisprudence OR standards OR statistics)) OR (behavioral sciences[mh] AND prisons[mh] and legislation)',
    );
  reset($terms);
  return join(' OR ', array_values($terms));
}
