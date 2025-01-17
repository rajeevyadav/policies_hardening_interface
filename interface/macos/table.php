<?php

$link_csv_file = "data/finding_list_machine_UIX.csv";


////////////////////////////////////////////////////////////////////////////////
////////////////////////////////  Variable part ////////////////////////////////
////////////////////////////////////////////////////////////////////////////////


/* Global variable is used for keep the last category */
$global_categ = "init";
$done_policies_nbr = 0;
$checked_policies_nbr = 0;
$policies_nbr = 0;


////////////////////////////////////////////////////////////////////////////////
////////////////////////////////  Function part ////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/* sanitize class and div */
// Input : String
// Output : String
// Example :
// [hello world 1234:5]  to  [hello-world-12345]
function sanitizer($string){
   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
   return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}

/* clean text */
// Input : String
// Output : String
// Example :
// [->item1 \n ->item2]  to  [<ul><li>item1</li><li>item2</li></ul>]
function clean_text($string){
  $string_content = $string;
  $first_occur_pos = strpos($string, '->');
  if ($first_occur_pos !== false) {
    //replace first occurence
    $string_content = substr_replace($string_content, "<ul><li>", $first_occur_pos, strlen('->'));
    $string_content = str_replace("->", "</li><li>", $string_content);
    $string_content = $string_content."</li></ul>";
  }
  $string_content = str_replace("\n", "<br>", $string_content);
  return $string_content;
}

/* write_tr function is used to print row's policies */
// Input : Array
function write_tr($data)
{
  global $global_categ;
  list($id, $category, $name, $assessment_status, $method,
  $method_option, $get_command, $set_command, $user,
  $registry_path, $registry_item, $default_value, $recommended_value, $type_value, $operator, $severity,
  $level, $graphical_method, $UIX_impact, $use, $use_mode, $intro, $link_for_more_infos, $tags,
  $consequences, $advice, $notes, $comment, $possible_values, $operting_system) = $data;

  /* we clean id content because it is used in href */
  $id = sanitizer($id);

  /* category title printing */
  if ($global_categ!=$category) {
    $categoty_content = preg_replace("/[^a-zA-Z0-9]+/", "", $category);
    echo "
    <tr id=\"$categoty_content-$id\">
      <td colspan=\"8\" class=\"table-secondary title-tr\">
        <h4>$category</h4>
      </td>
    </tr>
    ";
    $global_categ = $category;
  }


  /**** coloring part ****/
  // Color code :
  // table-danger : policy whose introduction is not written (considered not done)
  // table-warning : policy whose UIX_impact category is defined with '?' (considered not completed)
  // table-orange : policy whose use category is defined with '?' (considered not completed)
  global $done_policies_nbr;
  global $checked_policies_nbr;
  $class_content = "";
  if ($intro=="") {
    $class_content = "table-danger";
    $done_policies_nbr = $done_policies_nbr+1;
  }else {
    if ($UIX_impact=='?') {
      $class_content = "table-warning";
    }
    if ($use=='?') {
      $class_content = "table-orange";
    }
  }
  if ($use=="1") {
    $checked_policies_nbr = $checked_policies_nbr+1;
  }
  /********/

  $class_content_global = "content-$id";


  /**** Compute some values before row printing ****/

  /* severity */
  switch ($severity) {
    case 'High':
      $severity="<span class=\"badge bg-danger\">High</span>";
      break;
    case 'Medium':
      $severity="<span class=\"badge bg-secondary\">Medium</span>";
      break;
    case 'Low':
      $severity="<span class=\"badge bg-primary\">Low</span>";
      break;
  }

  /* UIX impact */
  $UIX_impact_content = "<span class=\"badge bg-secondary\">Not defined</span>";
  switch ($UIX_impact) {
    case '2':
      $UIX_impact_content="<span class=\"badge bg-danger\">Impact</span>";
      break;
    case '1':
      $UIX_impact_content="<span class=\"badge bg-warning text-dark\">Potentially</span>";
      break;
    case '0':
      $UIX_impact_content="<span class=\"badge bg-success\">No impact</span>";
      break;
  }

  /* default value */
  $default_value_content = str_replace(";", ";<br>", $default_value);
  $default_value_content = "<code>$default_value_content</code>";

  /* recommended value */
  $recommended_value_content = str_replace(";", ";<br>", $recommended_value);
  $recommended_value_content = "<code>$recommended_value_content</code>";

  /* use check box */
  $check_box_value = "not-checked";
  if ($use=="1") {
    $check_box_value="checked";
  }

  /* Mode */
  $use_mode_content="";
  if ($use_mode) {
    switch ($use_mode) {
      case 'Basic':
        $use_mode_content = "<span class=\"badge bg-light text-dark m-1\" style=\"border:solid 1px;\">$use_mode</span>";
        break;
      case 'Enterprise':
        $use_mode_content = "<span class=\"badge bg-secondary m-1\">$use_mode</span>";
        break;
      case 'StrongBox':
        $use_mode_content = "<span class=\"badge bg-dark m-1\">$use_mode</span>";
        break;

      default:
        // code...
        break;
    }
  }

  /**** Row printing ****/
  echo"
  <tr id=\"data-$id\" class=\"$class_content $class_content_global tr-visible row-content\" csv-data=\"$id,$recommended_value,$check_box_value\" data-tags=\"$tags\" active-filter-uix=\"false\" active-filter-severity=\"false\">
    <th class=\"btn-link csv-id\" scope=\"row\" data-bs-toggle=\"collapse\" data-bs-target=\"#data-content-$id\" role=\"button\" data-target=\"#data-content-$id\">
      <a href=\"#data-$id\" >$id</a>
    </th>
    <td class=\"mode\"> $use_mode_content</td>
    <td>$name</td>
    <td class=\"severity\" data-content=\"data-content-$id\" >$severity</td>
    <td class=\"uix_impact\" >$UIX_impact_content</td>
    <td>$default_value_content</td>
    <td class=\"value_to_use\">$recommended_value_content</td>
    <td>
      <input class=\"form-check-input check-policy\" type=\"checkbox\" value=\"\" $check_box_value>
    </td>
  </tr>";


  //**** Compute some values before content printing ****//
  /* intro */
  $intro_content = clean_text($intro);

  /* Graphical Method */
  $graphical_method_content = "";
  if ($graphical_method!="") {
    $graphical_method_content = clean_text($graphical_method);
    $graphical_method_content="<h3>Graphical Method</h3><hr><div class=\"alert alert-primary\" role=\"alert\">$graphical_method_content</div>";
  }

  /* Consequences */
  $consequences_content = "";
  if ($consequences!="") {
    $consequences_content = clean_text($consequences);
    $consequences_content="<h4>Potential impact</h4><div class=\"alert alert-danger\" role=\"alert\">$consequences_content</div>";
  }

  /* Advices */
  $advice_content = "";
  if ($advice!="") {
    $advice_content = clean_text($advice);
    $advice_content="<h4>Advices</h4><div class=\"alert alert-success\" role=\"alert\">$advice_content</div>";
  }

  /* Notes */
  $notes_content = "";
  if ($notes) {
    $notes_content = clean_text($notes);
    $notes_content="<h4>Notes</h4><div class=\"alert alert-secondary\" role=\"alert\">$notes_content</div>";
  }

  /* OS */
  $operting_system_content = "";
  if ($operting_system) {
    $operting_system_content = "<h4>OS</h4><div class=\"alert alert-primary\" role=\"alert\">$operting_system</div>";
  }

  /* command */
  $command_content ="";
  if (strcmp($method, "Registy")) {
    $command_get = "<span class=\"code-command\">Get-ItemProperty </span><span class=\"code-option\">-path </span><span class=\"code-argument\">$registry_path </span><span class=\"code-option\">-name </span><span class=\"code-argument\">'$registry_item'</span>";
    $command_set = "<span class=\"code-command\">Set-ItemProperty </span><span class=\"code-option\">-path </span><span class=\"code-argument\">$registry_path </span><span class=\"code-option\">-name </span><span class=\"code-argument\">'$registry_item'</span><span class=\"code-option\">-value </span><span class=\"code-argument\">$recommended_value</span>";
    $command_content = "
    <h4>Powershell Command</h4>
    <h6>Get Value : </h6>
    <pre> $command_get</pre>
    <h6>Set Value : </h6>
    <pre> $command_set</pre>
    <br>";
  }


  /* Method */
  $method_content="";
  if ($method) {
    $method_argument_content = "";
    if ($method_argument) {
      $method_argument_content = "<h6>Method Argument :</h6><pre><code>$method_argument</code></pre></li>";
    }
    $method_content = "
    <h4>Method</h4>
    <h6>Method : </h6>
    <pre> <code>$method</code></pre>
    $method_argument_content
    <br>";
  }

  /* Registy */
  $registry_content="";
  if ($registry_path) {
    //$registry_path_content = str_replace("\\", "<br>\\", $registry_path);
    $registry_content = "
    <h4>Registy</h4>
    <h6>RegistryPath : </h6>
    <pre><code>$registry_path</code></pre>
    <h6>RegistryItem : </h6>
    <pre><code>$registry_item</code></pre>
    <br>";
  }

  /* Tags */
  $tags_content="";
  if ($tags) {
    $tags_array = explode(";", $tags);
    foreach ($tags_array as $tag) {
      $tags_content = "$tags_content<span class=\"badge rounded-pill bg-dark m-1\">$tag</span>";
    }
  }

  /* Values */
  $possible_values_array = explode(":", $possible_values);
  $type = $possible_values_array[0];
  $values = $possible_values_array[1];
  $values = str_replace(";", "</li><li>", $values);

  //**** Blank row printing ****//
  echo "<tr class=\"$class_content_global\" ></tr>";

  //**** Content row printing ****//
  echo "
  <!---- Toggle content ---->
  <tr class=\"$class_content_global\">
    <td colspan=\"8\" class=\"hiddenRow\">
      <div id=\"data-content-$id\" class=\"accordian-body collapse\" style=\"\">
        <div class=\"px-2\">

          <!---- Header content ---->
          <div>
            <h2>$name</h2>
            <div>$tags_content</div>
          </div>

          <hr>

          <!---- Data content ---->
          <div class=\"row align-items-start\">

            <!-- Introduction -->
            <div class=\"col-4\">
              <h3>Introduction</h3>
              <hr>
                <p>$intro_content</p>
                <a target=\"_blank\" href=\"$link_for_more_infos\">Read more ></a>
              <br>
              <br>
              $graphical_method_content
            </div>


            <!-- Table of settings -->
            <div class=\"col-4\">
                <h3>Table of settings</h3>
                <hr>
                  <h4>UIX</h4>
                  $UIX_impact_content : <code>$UIX_impact</code>
                  <br>
                  <br>
                  $method_content
                  $registry_content
                  <h4>Values</h4>
                  <h6>Type : </h6><code>$type</code>
                  <h6>Possible Values : </h6>
                  <code>
                    <ul>
                      <li>$values</li>
                    </ul>
                  </code></li>
                  <div>
                  </div>
            </div>

            <!-- More Informations -->
            <div class=\"col-4\">
              <h3>More Informations</h3>
              <hr>
                $consequences_content
                $advice_content
                $notes_content
                $operting_system_content
            </div>

          </div>
          <!---- End data content ---->
        </div>
      </div>
    </td>
  </tr>
  <!---- End toggle content ---->";



}


////////////////////////////////////////////////////////////////////////////////
////////////////////////////////  Main part ////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$row = 0;
if (($handle = fopen($link_csv_file, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 3000, ",")) !== FALSE) {
        if ($row>0) {
          write_tr($data);
        }

        $row++;
    }
    fclose($handle);
}


////////////////////////////////////////////////////////////////////////////////
//////////////////////////////  Progress view //////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$policies_nbr = $row-1;
$done_policies_nbr = $policies_nbr-$done_policies_nbr;
$calc_global = intval($done_policies_nbr*100/$policies_nbr);
$calc_goal = intval($checked_policies_nbr*100/50);
echo "
<!---- Progress view ---->
<div class=\"alert alert-primary\" role=\"alert\" style=\"max-width:400px;\">
  <h4>Global progress - $done_policies_nbr/$policies_nbr</h4>
  <div class=\"progress\">
    <div class=\"progress-bar progress-bar-striped bg-success\" role=\"progressbar\" style=\"width: $calc_global%;\" aria-valuenow=\"$calc_global\" aria-valuemin=\"0\" aria-valuemax=\"100\">$calc_global%</div>
  </div>
  <h4>Goal progress - $checked_policies_nbr/50</h4>
  <div class=\"progress\">
    <div class=\"progress-bar progress-bar-striped bg-success\" role=\"progressbar\" style=\"width: $calc_goal%;\" aria-valuenow=\"$calc_goal\" aria-valuemin=\"0\" aria-valuemax=\"100\">$calc_goal%</div>
  </div>
</div>
<!---- End progress view ---->
";



 ?>
