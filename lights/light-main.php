<!DOCTYPE html>
<html>
<body>

<button type="button" onclick="loadDoc('5594892', 'LIGHT')">Badroom light</button>
<button type="button" onclick="loadDoc('5594928', 'LIGHT')">Badroom small</button>
<br>
<button type="button" onclick="loadDoc('6116620', 'LIGHT')">Hallway light</button>
<br>
<button type="button" onclick="loadDoc('5602563', 'LIGHT')">Living room 1</button>
<button type="button" onclick="loadDoc('5602572', 'LIGHT')">Living room 2</button>
<button type="button" onclick="loadDoc('5592405', 'LIGHT')">Living room 3</button>


<br>
<button type="button" onclick="loadDoc('ON', 'AC')">AC ON</button>
<button type="button" onclick="loadDoc('OFF', 'AC')">AC OFF</button>

<script>
function loadDoc(param, device) {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (xhttp.readyState == 4 && xhttp.status == 200) {
      document.getElementById("demo").innerHTML = xhttp.responseText;
    }
  };
if (device==="LIGHT"){
  xhttp.open("GET", "light.php?code=" + param, true);
}
else if (device==="AC"){
  xhttp.open("GET", "ac.php?command=" + param, true);
}
  xhttp.send();
}
</script>

</body>
</html>

