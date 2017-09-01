# Amazon Alexa Skill: Seizure Tracker

The purpose of this repository is for development of the following Amazon Alexa skill that can be used on Amazon Echo and Echo Dot devices to track epileptic seizures using [SeizureTracker.com](https://www.seizuretracker.com).




# Details

There are three primary functions within [seizures.events.php](seizures.events.php) (which is called by [seizure.php](seizure.php)) that interact with the SeizureTracker.com API to do the following things via Alexa voice commands:

  * Count seizures that have occurred today
    - Alexa intent: `CountSeizures`
    - PHP function: `count_seizures`

  * Track a new seizure
    - Alexa intent `AddSeizure`
    - PHP function: `add_seizure`

  * Mark seizure as being over
    - Alexa intent: `EndSeizure`
      PHP function: `end_seizure`


## Future Plans?

Including these capabilities could be useful:
  * Track whether seizure medication was taken
  * Track vagal nerve stimulator (VNS) usage

"Flash Briefing" ideas:
  * Medication reminder
  * Announce count of seizures for the prior day
