function showInfo(id) {
  var x  = document.getElementById(id);
  var xs = document.getElementById(id+"_switch");
  if (x.hidden) {
    x.hidden     = false;
    xs.innerHTML = xs.innerHTML.replace("+", "-");
  } else {
    x.hidden     = true;
    xs.innerHTML = xs.innerHTML.replace("-", "+");
  }
}

function twitterSwitch(){
  var cb   = document.getElementById("twitter_checkbox")
  var astt = document.getElementById("ast_twitts")
  var allt = document.getElementById("all_twitts")
  if (cb.checked) {
    astt.hidden = false
    allt.hidden = true
  } else {
    astt.hidden = true
    allt.hidden = false
  }
}
