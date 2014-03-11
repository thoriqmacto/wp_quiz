<!-- Template -->
<script type="text/template" id="inputTemplate">
    <label for="<%= answer_id %>"><%= index %>:</label>
    <input id="<%= answer_id %>" class="answers" size="30" type="text" name="<%= answer_id %>" value="<%= answer %>" placeholder="Answer for Question <%= index %> Here">
    <button disabled="true">Save</button>
</script>
<!-- End template -->

<p>Enter the Answers below</p>
<div id="answerInputs"></div>
<div id="answerSelect">
    <span>Correct Answer:</span>
</div>
<p>
    <input name="save" type="submit" class="button button-primary button-small" value="Save all">
</p>

<script>
  window.wpQuiz = {};
  var wpq = window.wpQuiz;
  wpq.answers = <?= $answers ?>;
  wpq.answers.correct = <?= $correct ?>;
  wpq.answerSelect = '#answerSelect';
  wpq.answerInput = '#answerInputs';
  wpq.inputTempl = '#inputTemplate';
  wpq.post_id = <?= $post->ID ?>;
</script>