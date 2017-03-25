# Alexa Seizure Tracking

The following documentation should explain how to set up this custom Alexa skill that can hopefully eventually work with [SeizureTracker.com](https://www.seizuretracker.com).

Right now it simply works with a small MySQL database, but in the future should use an API making it so that you can do all of the following with Alexa voice commands to an Amazon Echo (dot):

  * Track a seizure
  * Track said seizure as being over
  * Count seizures that have occurred today

---

## Local Setup

To get started, you will want to visit this [AWS Alexa Developer](https://developer.amazon.com/edw/home.html#/) page, click "Get Started >" under "Alexa Skills Kit", then click the "Add a New Skill" button in the top right corner.

---

### Skill Information

You want to create a "Custom" skill named any thing that you would like, and the invocation name should probably be `seizuretracker`:

![Alexa Skill Information Screenshot](https://raw.githubusercontent.com/ericoc/alexa-testing/master/seizure/images/skill-info.png "Alexa Skill Information Screenshot")

---

### Interaction Model

These settings specify how users interact with Alexa based on their "intent" (Amazon's verbage). This is where the magic happens. The things outlined and defined here determine what is in the (JSON within the) HTTPS request sent to the web service URL specified later.

  * Intent Schema
    - For the "Intent Schema", simply paste the JSON contents of the [seizure-intents.json file from this repository](seizure-intents.json) in to the textarea.

  * Custom Slot Types
    - Create the custom slot types as defined within the [slot-types.txt file from this repository](slot-types.txt) in this repository.

  * Sample Utterances
    - Additionally, enter the sample utterances from the [sample-utterances.txt file from this repository](sample-utterances.txt) in this repository.

---

### Configuration

Within the "Configuration" page, select the following:

  * Service Endpoint Type
    - HTTPS
  * Geographical Region
    - North America
  * URL
    - `https://alexa.ericoc.com/seizure/seizure.php`
    - (This is simply the script [here](seizure.php))

  * Select "No" regarding Account Linking (for now).

![Alexa Skill Configuration Screenshot](https://raw.githubusercontent.com/ericoc/alexa-testing/master/seizure/images/configuration.png "Alexa Skill Configuration Screenshot")

---

### SSL Certificate

Simply select the second option of:

`My development endpoint is a sub-domain of a domain that has a wildcard certificate from a certificate authority`

![Alexa Skill SSL Certificate Screenshot](https://raw.githubusercontent.com/ericoc/alexa-testing/master/seizure/images/ssl-certificate.png "Alexa Skill SSL Certificate Screenshot")

---

### Test > Service Simulator

Finally, an easy way to test all of the above is to enter a phrase such as one of the following in to the "`Enter Utterance`" field:

  *	`Track a seizure`
    - should hopefully return a valid JSON response that you can listen to within the browser!

  * `Seizure is over`
    -  should also return a valid JSON response indicating that the previous seizure you recorded has been marked as over.

  * `Count seizures`
    - should let you track the number of seizures that are stored in the database for the current date

---

## Conclusion

This Alexa skill should also be available locally on your Echo (dot) device which you can confirm by visiting [alexa.amazon.com](http://alexa.amazon.com/spa/index.html#skills/your-skills/?ref-suffix=ysa_gw), searching for the name of the skill that you set originally, and ensuring that it is Enabled.

The only caveat to testing it with your voice on your actual device is that you have to ensure that you are within the Alexa skill by using the invocation word that you set in the beginning. This means that you have to phrase your voice commands like so:

  * "*Alexa, tell SeizureTracker to* **track a seizure**."
  * "*Alexa, tell SeizureTracker that the* **seizure is over**."
  * "*Alexa, ask SeizureTracker to* **count seizures**?"

Different phrasing variations of these all work partially based on words Amazon automatically ignores (like "to" and "the", I think) as well as the Interaction Model defined so you can try lots of different stuff!
