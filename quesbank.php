<html>
<head>
<style>
p1 { margin-left: 30% } 
form {
   margin-left: 30%; 
}
table {
  width: 50%; 
}
th {
  height: 50px; 
}
tr {
  width: 100%; 
  font-family: "Times New Roman", Times, serif; 
}
</style> 
</head>
<body background = "light.jpg" >

<h1> Mock Instructor Page </h1>
<h2> Question Bank </h2> 
<p1> Please retrieve a question: 1 to 4.</p1><br> 
<p1 id="usr"></p1>
<p2 id="test"></p2>
<form id="form" >
<select id = "qnum">
     <option value="1"> 1 </option>
     <option value="2"> 2 </option>
     <option value="3"> 3 </option>
     <option value="4"> 4 </option> 
</select>
     <button type="button" onclick="shootPROJ()">Submit</button> 
</form>

<table id = "table" >
  <tr>
       <th>Question </th>
       <th> </th> 
       <th> </th>
       <th> </th> 
  </tr>
  <tr>
       <th id="a1" >question 1 here.. </th>
       <th id="a2" ></th>
       <th id="a3" > </th>
       <th id="a4" > </th>
   </tr> 
</table>
</body>
</html>

<script>
function shootPROJ() { 
    var ajaxRequest; 
    var projectQ; 
    
    alert("starting ajax request..."); 
    try { 
       ajaxRequest = new XMLHttpRequest(); 
    } catch (e) {
       alert(e.message); 
    }

    ajaxRequest.onreadystatechange = function() {
          console.log(ajaxRequest.readyState); 
          if (ajaxRequest.readyState == 4) {
	     console.log(ajaxRequest.responseText); 
	     console.log(this.getAllResponseHeaders());
	     var target = JSON.parse(ajaxRequest.responseText); 
	     document.getElementById('a1').innerHTML  = target.question;  
         //    document.getElementById('table').row[1].cell[0].innerHTML = ajaxRequest.responseText; 
	  } else
	  {
             console.log("function failed"); 
	  }  

    }//function() 
        var qnum = document.getElementById("qnum"); 
	var qsel = qnum.options[qnum.selectedIndex].value; 
	console.log("sending " + qsel); 
	var projectQ = {"qnum" : qsel}; 
        document.getElementById("usr").innerHTML = "you selected " + qsel;  
        ajaxRequest.open("POST", "https://web.njit.edu/~wbv4/Middle/midlogin.php", true);
	ajaxRequest.send(JSON.stringify(projectQ)); 
	
    return false; 
} //shootPROJ
</script>
