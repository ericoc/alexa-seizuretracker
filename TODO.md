## stuff I should do to improve this (aka. TODO)

#### primary goals

* ~~actually hook in to the [seizuretracker.com](https://seizuretracker.com/) API!~~ done!
* ~~Account Linkage to SeizureTracker.com~~ see [login.php](login.php) - done!
* ~~add seizures~~ see `add_seizure` function within [seizure.events.php](seizure.events.php) - done!
* need to be able to update end date of most recent seizure within `EndSeizure` intent (`end_seizure` function) - NEXT UP
* actually get it submitted to Amazon for certification and hopefully get published as an Alexa Skill for any one to use maybe?!

---

#### others

* `class{}`-y PHP would be great.
* perhaps use BriefingOut function (from `alexa.func.php`) in some way to announce count of seizures for the prior day (yesterday) or really anything...?
* [this](https://github.com/ericoc/alexa-testing/blob/master/seizure/seizure.php#L31); where I just accept whatever, if it got a string back
* maybe do more intense checking on phrases received from Alexa?
  - it absolutely cannot do proper voice-to-text on the word *"ended"* no matter how I pronounce it
      * I've gotten back *"and 8"*, *"I ate and"*, and all kinds of crazy stuff that's not even close
  - just waiting may fix this since hopefully Alexa speech-to-text will improve with time

