# Alexa Skill Development for SeizureTracker.com

The following documentation should explain how to set up this custom Alexa skill that can hopefully eventually work with [SeizureTracker.com](https://www.seizuretracker.com).

The goal here is to use the SeizureTracker.com API and allow any one to do the following things with Alexa voice commands to an Amazon Echo (or Echo Dot!):

  * Track a seizure (`LogSeizure`)
  * Track most recent seizure as being over (`UpdateSeizure`)
  * Count seizures that have occurred today (`CountSeizures`)

---

## Local Setup

To get started, you will want to visit this [AWS Alexa Developer](https://developer.amazon.com/edw/home.html#/) page, click "Get Started >" under "Alexa Skills Kit", then click the "Add a New Skill" button in the top right corner.

---

### Skill Information

You want to create a "Custom" skill:

  * Name
    - SeizureTracker
  * Invocation Name
    - "seizure tracker"
  * Global Fields
    - Audio Player
      - No

---

### Interaction Model

These settings specify how users interact with Alexa based on their "intent" (Amazon's verbage). This is where the voice magic happens.

The things defined here determine what is in the (JSON within the) HTTPS request that gets sent to our web service URL specified later.

#### Intent Schema

"Intent Schema" is a JSON outline of what custom slot types can be used with what intents and how they will be identified in the sample utterances.

  * Simply paste the JSON contents of the [intent-schema.json file from this repository](intent-schema.json) in to the text area.

#### Custom Slot Types

"Custom Slot Types" are basically pre-defined variables that can be referenced within the different intents to use in sample utterances.

  * Create the custom slot types as defined within the [slot-types.txt file from this repository](slot-types.txt) in this repository.

#### Sample utterances

"Sample Utterances" are example phrases that users would say. These are how Alexa determines what the users intent is - based on the words that they said. The name of the intent given in the sample utterance is what is used within our web service to determine how to proceed and interact with the SeizureTracker API.

  * Enter the sample utterances from the [sample-utterances.txt file from this repository](sample-utterances.txt) in this repository.

---

### Configuration

#### Endpoint

  * Service Endpoint Type
    - HTTPS
  * Geographical Region
    - North America
  * URL
    - `https://st.ericoc.com/seizure.php`
      - [seizure.php in this repository](seizure.php)

#### Account Linking

  * Authorization URL
    - `https://st.ericoc.com/login.php`
      - [login.php in this repository](login.php)
  * Client ID
    - `seizuretracker`
  * Domain List
    - `seizuretracker.com`
    - `www.seizuretracker.com`
    - `st.ericoc.com`
  * Authorization Grant Type
    - Implicit Grant

---

### SSL Certificate

Simply select the second option of:

`My development endpoint is a sub-domain of a domain that has a wildcard certificate from a certificate authority`

---

### Test > Service Simulator

Make sure that "Enabled" is selected so that the skill is enabled on your account to test with your Echo (dot) device.

Finally, an easy way to test all of the above is to enter a phrase such as one of the following in to the "`Enter Utterance`" field:

  *	`Track a seizure`
    - should hopefully return a valid JSON response that you can listen to within the browser!

  * `Seizure is over`
    -  should also return a valid JSON response indicating that the previous seizure you recorded has been marked as over.

  * `Count seizures`
    - should let you track the number of seizures that are stored in the database for the current date

---

## Conclusion

This Alexa skill should also be available locally on your Echo (dot) device which you can confirm by visiting [alexa.amazon.com](http://alexa.amazon.com/spa/index.html#skills/your-skills/?ref-suffix=ysa_gw), searching for the name of the skill that you set originally, and ensuring that it is Enabled as well as linked to your SeizureTracker.com account.

The only caveat to testing it with your voice on your actual device is that you have to ensure that you are within the Alexa skill by using the invocation word that you set in the beginning. This means that you have to phrase your voice commands like so:

  * "*Alexa, tell SeizureTracker to* **track a seizure**."
  * "*Alexa, tell SeizureTracker that the* **seizure is over**."
  * "*Alexa, ask SeizureTracker to* **count seizures**?"

Different phrasing variations of these all work partially based on words Amazon automatically ignores (like "to" and "the", I think?) as well as the Interaction Model defined so you can try lots of different stuff!

---

### Future Plans?

Including these capabilities could be useful:
  * Track whether seizure medication was taken
  * Track vagal nerve stimulator (VNS) usage

"Flash Briefing" ideas:
  * Medication reminder
  * Announce count of seizures for the prior day
