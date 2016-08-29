(function($){
	//*************************************************************************************************
	
	// Register the TI namespace
	window.TI = window.TI || {};
	
	/**
	 * Creates a new Question object, with the specified correct answer and the specified options
	 * @param correctAnswer Should be A through A + # of options
	 * @param questionType The type of question this question is
	 * @param explainationSelector The url to the explaination that will be displayed in a popup
	 * @param answerKey String of possible answers (ABCD).
	 * @param questionText The optional question text, will populate it with the question # in the section it belongs to
	 */
	window.TI.Question = function(correctAnswer, questionType, explainationSelector, answerKey, questionText, questionId)
	{
		this.questionId = questionId;
		this.id = 'st_question_' + this.questionId;
		this.correctAnswer = correctAnswer.toUpperCase();
		this.questionType = questionType;
		this.explainationSelector = explainationSelector;
		this.answerKey = answerKey;
		this.text = questionText;
		this.answers = {};
		var self = this;
		answerKey.split('').forEach( function( key ) {
			self.answers[key] = new TI.Answer( self.id, key );
		});
	};
	
	/**
	 * Creates a new Answer object, with the specified id, value and boolean for whether or not it's correct
	 * @param questionId The id of the question this answer belongs to
	 * @param answerValue The value of the answer
	 */
	window.TI.Answer = function(questionId, answerValue)
	{
		this.questionId = questionId;
		this.id = this.questionId + '_answer_' + answerValue;
		this.value = answerValue;
	};

	/**
	 * Creates a new ScoreTest object. Draws the UI in the specified container, with the specified question list
	 * @param _containerSelector The jQuery selector to put the container in 
	 * @param _questionsBySection TI.Question[] The list of Questions to render
	 */
	window.TI.ScoreTest = function(_containerSelector, _questionsBySection)
	{
		var _$parent = $(_containerSelector);
		var _$container = $(Template.ScoreTest).appendTo(_$parent);
		var _$sections = $('#st_sections', _$container);
		var _$sectionContainers = $('#st_sectionContainers', _$container);
		
		var _currentSection = null;
		var _sections = {};
		var isFirst = true;
		$.each(_questionsBySection, function(sectionName, questionsForSection) {
			// Make sure all the questions have question text of their # as a fallback to actually having text
			$.each(questionsForSection, function(q, question) {
				if( question ) {
					question.text = question.text || (q + 1);
				}
			});
		
			var $sectionTab = $(Template.SectionTab).text(sectionName).appendTo(_$sections).click(sectionClicked);
			var section = new TI.ScoreSection(_$sectionContainers, sectionName, questionsForSection, $sectionTab);
			_sections[sectionName] = section;
			if(isFirst)
			{
				showSection(sectionName);
			}
			
			isFirst = false;
		});
		
		// add the overall score tab
		var $sectionTab = $(Template.SectionTab).text('Overall').appendTo(_$sections).click(sectionClicked);
		_sections['Overall'] = new TI.BreakdownSection(_$sectionContainers,'Overall',[],$sectionTab);
		
		function sectionClicked()
		{
			showSection($(this).text());
		}
		
		function showSection(sectionName)
		{
			if(_currentSection)
			{
				_currentSection.hide();
			}
		
			_currentSection = _sections[sectionName];
			if(_currentSection)
			{
				_currentSection.show();
			}
		}
	};
	
	/**
	 * ScoreSection:
	 * This is the thing where user's input their answers
	 *
	 **/
	window.TI.ScoreSection = function(_$parent, _sectionName, _questions, _$sectionTab)
	{
		var _this = this;
		var _$container = $(Template.ScoreSection).appendTo(_$parent);
		var _$header = $('#st_sectionHeader', _$container).text('Score Section: ' + _sectionName);
		var _$questionsContainer = $('#st_questionsContainer', _$container);
		var _$questions = $('#st_questions', _$container);
		$('#st_scoreTest', _$container).click(scoreTest);
		$('#st_saveTest',_$container).click(saveTest);
		$('#st_getTest',_$container).click(getTest);
		var _$resultsContainer = $('#st_resultsContainer', _$container);
		var _$resultsSummaryContainer = $('#st_resultsSummaryContainer', _$container);
		var _$resultsByTypeTabs = $('#st_resultsByType', _$container);
		var _$resultsByTypeContainers = $('#st_resultsByTypeContainers', _$container);
		var _$results = $('#st_results', _$container);
		
		var _currentResultsType;
		var _resultsByType;
		
		hide();
		
		$.each(_questions, function(q, question) {
			/* question TI.Question */
			var $questionContainer = $(Template.ScoreTestQuestion).appendTo(_$questions).attr('id', question.id);
			$('.st_question', $questionContainer).text(question.text);
			var $answersContainer = $('.st_yourAnswer', $questionContainer);
			
			$.each(question.answers, function(a, answer) {
				/* answer TI.Answer */
				$(Template.ScoreTestAnswer).text(answer.value).attr('for', answer.id).appendTo($answersContainer);
				$(Template.ScoreTestAnswerChoice).attr({
					id: answer.id,
					name: answer.questionId,
					value: answer.value
				}).appendTo($answersContainer);
			});
		});
		
		_this.show = show;
		function show()
		{
			_$container.show();
			_$sectionTab.addClass('st_selectedTab');
		}
		
		_this.hide = hide;
		function hide()
		{
			_$container.hide();
			_$sectionTab.removeClass('st_selectedTab');
		}
		
		// user clicks the save test button
		function saveTest()
		{
			var testName = $('#testName').html();
			var section = _sectionName.match(/[1-4]/)[0];
			var userAnswers = {};
			var breakdown = {};
			$('input[type=radio]:checked', _$questions).each(function(i, el) {
				var $el = $(el);
				userAnswers[$el.attr('name')] = $el.val();
			});
			$.ajax({
				type: "POST",
				url: "/scores2.php",
				data: { t: testName, a: userAnswers, s : section, d : 'save' }, 
				dataType: "json",
				success: function(data, textStatus) {},
				error: function() {alert("failed to save test");}
			});
		}
		
		// user clicks get test button
		// look for their answers and fill in the radio buttons
		function getTest()
		{
			var testName = $('#testName').html();
			var section = _sectionName.match(/[1-4]/)[0];
			$('input[type=radio]:checked', _$questions).each(function(i, el) {
				var $el = $(el);
				$el.attr('checked',false);
			});
			$.ajax({
				type: "POST",
				url: "/scores2.php",
				data: { t: testName, s: section, d : 'get'	},
				dataType: "json",
				success: function( data ) {
					if( !data || !data.answers ) return;
					$.each(data.answers, function(key, value) {
						var id = key + '_answer_' + value;
						$('#'+id).attr('checked',true);
					});						
					},
				error: function() {alert("failed to load test");}
			});
		}
		
		// user clicks score test button
		// save test
		// get scores
		// print out scores
		function scoreTest()
		{
			var testName = $('#testName').html();
			var section = _sectionName.match(/[1-4]/)[0];
			var userAnswers = {};
			$('input[type=radio]:checked', _$questions).each(function(i, el) {
				var $el = $(el);
				userAnswers[$el.attr('name')] = $el.val();
			});
			$.ajax({
				type: "POST",
				url: "/scores2.php",
				data: { t: testName, a: userAnswers, s : section, d : 'score' }, 
				dataType: "json",
				success: function(data, textStatus) {
						if( !data || !data.breakdowns ) return;
						_$questionsContainer.hide();
						// show the correct answers:
						$.each(_questions, function(q, question) {
							var userAnswer = userAnswers[question.id];
							var isCorrect = userAnswer == question.correctAnswer;
							var questionType = question.questionType;
							
							var $resultContainer = $(Template.ScoreTestResult).appendTo(_$results);
							$('.st_question', $resultContainer).text(question.text);
							$('.st_questionType', $resultContainer).text(question.questionType);
							$('.st_yourAnswer', $resultContainer).text(userAnswer ? userAnswer : '-').addClass(isCorrect ? 'st_correctAnswer' : 'st_incorrectAnswer');
							$('.st_correctAnswer', $resultContainer).text(question.correctAnswer);
							if(question.explainationSelector)
							{
								$('.st_explaination', $resultContainer).colorbox({ inline: true, href: question.explainationSelector, innerWidth: '60%' });
							}
						});
						// show the breakdowns:
						_resultsByType = {};
						$.each( data.breakdowns, function( type, breakdown ) {
							_resultsByType[type] = new TI.ResultSummary(type);
							_resultsByType[type].load( breakdown.numberQuestions, breakdown.numberAnswersCorrect, breakdown.numberAnswersOmitted, breakdown.numberAnswersIncorrect, breakdown.percentCorrect, breakdown.analysis);
						});	
						
						$.each(_resultsByType, function(questionType, results) {
							var $resultsTypeTab = $(Template.ScoreTestResultTab).appendTo(_$resultsByTypeTabs).click(showResultsByTypeClicked);
							var $breakdownContainer = $(Template.ScoreTestBreakdown).appendTo(_$resultsByTypeContainers);
							results.render($resultsTypeTab, $breakdownContainer);
						});
						
						showResultsByType('Overall');			
						_$resultsContainer.show();
							
					},
				error: function() {alert("failed to save test");}
			});
		}
		
		function showResultsByTypeClicked()
		{
			showResultsByType($(this).text());
		}
		
		function showResultsByType(questionType)
		{
			if(_currentResultsType)
			{
				_currentResultsType.hide();
				_currentResultsType = null;
			}
			
			_currentResultsType = _resultsByType[questionType];
			if(_currentResultsType)
			{
				_currentResultsType.show();
			}
		}
	};
	
	/**
	 * Final breakdown that goes on the last page
	 * A bit of code gets duplicated here
	 *
	 **/
	window.TI.BreakdownSection = function(_$parent, _sectionName, _questions, _$sectionTab)
	{
		var _this = this;
		
		var _$StudentGrade = $(Template.StudentGrade).appendTo(_$parent);
		$('#st_sectionHeader', _$StudentGrade).text('What is your current grade?');
		$('#st_studentGradeSelect').change(function() { SaveStudentGrade(this) });
		
		var _$container = $(Template.BreakdownSection).appendTo(_$parent);
		var _$header = $('#st_sectionHeader', _$container).text('Test Results: ' + _sectionName);
		var _$resultsContainer = $('#st_resultsContainer', _$container);
		var _$resultsSummaryContainer = $('#st_resultsSummaryContainer', _$container);
		var _$resultsByTypeTabs = $('#st_resultsByType', _$container);
		var _$resultsByTypeContainers = $('#st_resultsByTypeContainers', _$container);
		var _$results = $('#st_results', _$container);
				
		var _currentResultsType;
		var _resultsByType;
		
		hide();
		
		_this.show = show;
		function show()
		{
			//_$StudentGrade.show();
			_$container.show();
			_$sectionTab.addClass('st_selectedTab');
			_$resultsByTypeTabs.empty();
			_$resultsByTypeContainers.empty();
			getBreakdown();
		}
		
		_this.hide = hide;
		function hide()
		{
			_$container.hide();
			_$StudentGrade.hide();
			_$sectionTab.removeClass('st_selectedTab');
		}
		
		function SaveStudentGrade(node)
		{
			if( !node ) return;
			var testName = $('#testName').html();
			var grade = parseInt($(node).find(":selected").text());
			if( !grade ) { console.log(grade); return }
			$.ajax({
				type: "POST",
				url: "/scores2.php",
				data: { d: 'studentgrade', t: testName, g: grade },
				dataType: "json",
				success: function( data ) {
					show();
					},
				error: function(){alert("failed to save grade");}
			});
		}
		
		function getBreakdown()
		{
			var testName = $('#testName').html();
			$.ajax({
				type: "POST",
				url: "/scores2.php",
				data: { d: 'breakdown', t: testName, q: 'Overall' },
				dataType: "json",
				success: function( data ) {
					if( !data || !data.breakdowns ) return;
					if( !data.studentGrade || parseInt(data.studentGrade) === 0 ) {
						_this.hide();
						_$sectionTab.addClass('st_selectedTab');
						_$StudentGrade.show();
						return;
					} else {
						$('#st_studentGradeSelect').val(data.studentGrade);
						_$StudentGrade.hide();
					}
					_resultsByType = {};
					$.each( data.breakdowns, function( type, breakdown ) {
						_resultsByType[type] = new TI.ResultSummary(type);
						if( breakdown )
							_resultsByType[type].load( breakdown.numberQuestions, breakdown.numberAnswersCorrect, breakdown.numberAnswersOmitted, breakdown.numberAnswersIncorrect, breakdown.percentCorrect, breakdown.analysis);
					});	
					$.each(_resultsByType, function(questionType, results) {
						var $resultsTypeTab = $(Template.ScoreTestResultTab).appendTo(_$resultsByTypeTabs).click(showResultsByTypeClicked);
						var $breakdownContainer = $(Template.ScoreTestBreakdownCumulative).appendTo(_$resultsByTypeContainers);
						results.render($resultsTypeTab, $breakdownContainer);
					});
					showResultsByType('Overall');			
					_$resultsContainer.show();
					},
				error: function(){alert("failed to load results");}
			});
		}
		
		function showResultsByTypeClicked()
		{
			showResultsByType($(this).text());
		}
		
		function showResultsByType(questionType)
		{
			if(_currentResultsType)
			{
				_currentResultsType.hide();
				_currentResultsType = null;
			}
			
			_currentResultsType = _resultsByType[questionType];
			if(_currentResultsType)
			{
				_currentResultsType.show();
			}
		}
	};
	
	/**
	 * This is a breakdown object, it contains the overall grades
	 *
	 **/
	window.TI.ResultSummary = function(questionType)
	{
		var _this = this;
		_this.questionType = questionType;
		_this.numberQuestions = 0;
		_this.numberAnswersCorrect = 0;
		_this.numberAnswersOmitted = 0;
		_this.numberAnswersIncorrect = 0;
		_this.percentCorrect = 0;
		_this.analysis = 0;
		
		var _$resultsTypeTab;
		var _$breakdownContainer;
		
		/**
		 * Adds the data directly instead of scoring answers
		 * @param { numberQuestions : x, numberAnswersCorrect : y, ... }
		 */
		_this.load = function(numberQuestions,numberAnswersCorrect,numberAnswersOmitted,numberAnswersIncorrect,percentCorrect,analysis)
		{
			_this.numberQuestions = numberQuestions;
			_this.numberAnswersCorrect = numberAnswersCorrect;
			_this.numberAnswersOmitted = numberAnswersOmitted;
			_this.numberAnswersIncorrect = numberAnswersIncorrect;
			_this.percentCorrect = percentCorrect;
			_this.analysis = analysis;
		}
		
		_this.render = function($resultsTypeTab, $breakdownContainer)
		{
			_$resultsTypeTab = $resultsTypeTab;
			_$breakdownContainer = $breakdownContainer;
			
			_$resultsTypeTab.text(questionType);
			$('.st_breakdownNumberQuestions', _$breakdownContainer).text(_this.numberQuestions);
			$('.st_breakdownNumberAnswersCorrect', _$breakdownContainer).text(_this.numberAnswersCorrect);
			$('.st_breakdownNumberAnswersIncorrect', _$breakdownContainer).text(_this.numberAnswersIncorrect);
			$('.st_breakdownNumberAnswersOmitted', _$breakdownContainer).text(_this.numberAnswersOmitted);
			$('.st_breakdownPercentCorrect', _$breakdownContainer).text(_this.percentCorrect + '%');
			if( $('.st_breakdownAnalysis', _$breakdownContainer) )
				$('.st_breakdownAnalysis', _$breakdownContainer).text(_this.analysis);
			
			hide();
		};
		
		_this.show = show;
		function show()
		{
			_$resultsTypeTab.addClass('st_selectedTab');
			_$breakdownContainer.show();
		}
		
		_this.hide = hide;
		function hide()
		{
			_$resultsTypeTab.removeClass('st_selectedTab');
			_$breakdownContainer.hide();
		}
	};
	
	
	var sites = ['isee','ssat','shsat','hspt'];
	var Template = {};
	var DEBUG = false;
	sites.forEach(function(site) {
		if( typeof document !== 'undefined' && document.URL.match( site ) ) {
			Template.site = site;
		}
	});
	if( typeof document !== 'undefined' && document.URL.match( 'DEBUG' ) ) {
		DEBUG = true;
	}
	
	Template.ScoreTest =
		'<div id="st_container">' +
			'<ul id="st_sections"></ul>' +
			'<div id="st_sectionContainers"></div>' +
		'</div>';
		
	Template.SectionTab = '<li class="st_sectionTab"></li>';
	
	Template.ScoreSection =
		'<div class="st_sectionContainer">' +
			'<div id="st_sectionHeader"></div>' +
			'<div id="st_questionsContainer">' +
				'<table id="st_questions">' +
					'<tr><th>Question: </th><th>Your answer:</th></tr>' +
				'</table>' +
				'<input type="button" id="st_getTest" value="Load Saved Section"/>' +
				'<input type="button" id="st_saveTest" value="Save Section for later"/>' +
				'<input type="button" id="st_scoreTest" value="Save and Score Section"/>' +
			'</div>' +
			'<div id="`">' +
				'<div id="st_resultsSummaryContainer">' +
					'<table class="st_noBorderTable">' +
						'<tr>' +
							'<td class="st_resultsByTypeColumn"><ul id="st_resultsByType"></ul></td>' +
							'<td><div id="st_resultsByTypeContainers"></div></td>' +
						'</tr>' +
					'</table>' +
				'</div>' +
				'<table id="st_results">' +
					'<tr><th>Question: </th><th>Question Type: </th><th>Your answer: </th><th>Correct answer: </th><th>Explanation: </th></tr>' +
				'</table>' +
			'</div>' +
		'</div>';
	if( Template.site == 'shsat' || Template.site == 'ssat' ) Template.ScoreSection =
		'<div class="st_sectionContainer">' +
			'<div id="st_sectionHeader"></div>' +
			'<div id="st_questionsContainer">' +
				'<table id="st_questions">' +
					'<tr><th>Question: </th><th>Your answer:</th></tr>' +
				'</table>' +
				'<input type="button" id="st_getTest" value="Load Saved Section"/>' +
				'<input type="button" id="st_saveTest" value="Save Section for later"/>' +
				'<input type="button" id="st_scoreTest" value="Save and Score Section"/>' +
			'</div>' +
			'<div id="st_resultsContainer">' +
				'<div id="st_resultsSummaryContainer">' +
					'<table class="st_noBorderTable">' +
						'<tr>' +
							'<td class="st_resultsByTypeColumn"><ul id="st_resultsByType"></ul></td>' +
							'<td><div id="st_resultsByTypeContainers"></div></td>' +
						'</tr>' +
					'</table>' +
				'</div>' +
				'<table id="st_results">' +
					'<tr><th>Question: </th><th>Question Type: </th><th>Your answer: </th><th>Correct answer: </th></tr>' +
				'</table>' +
			'</div>' +
		'</div>';
	
	Template.StudentGrade = 
		'<div class="st_sectionContainer">' +
			'<div id="st_sectionHeader"></div>' +
			'<select id="st_studentGradeSelect">' +
				'<option selected="true" style="display:none;">Select your current grade</option>' +
				'<option value="3">3th</option>' +
				'<option value="4">4th</option>' +
				'<option value="5">5th</option>' +
				'<option value="6">6th</option>' +
				'<option value="7">7th</option>' +
				'<option value="8">8th</option>' +
				'<option value="9">9th</option>' +
				'<option value="10">10th</option>' +
				'<option value="11">11th</option>' +
				'<option value="12">12th</option>' +
			'</select>' +
			'<br/><br/>' +
		'</div>';
		
	Template.BreakdownSection =
		'<div class="st_sectionContainer">' +
			'<div id="st_sectionHeader"></div>' +
			'<div id="st_resultsContainer">' +
				'<div id="st_resultsSummaryContainer">' +
					'<table class="st_noBorderTable">' +
						'<tr>' +
							'<td class="st_resultsByTypeColumn"><ul id="st_resultsByType"></ul></td>' +
							'<td><div id="st_resultsByTypeContainers"></div></td>' +
						'</tr>' +
					'</table>' +
				'</div>' +
			'</div>' +
		'</div>';
		
	Template.ScoreTestResultTab = '<li class="st_resultTypeTab"></li>';
		
	Template.ScoreTestQuestion = 
		'<tr>' +
			'<td class="st_question"></td>' +
			'<td class="st_yourAnswer"></td>' +
		'</tr>';
		
	Template.ScoreTestResult =
		'<tr>' +
			'<td class="st_question"></td>' +
			'<td class="st_questionType"></td>' +
			'<td class="st_yourAnswer"></td>' +
			'<td class="st_correctAnswer"></td>' +
			'<td><a href="#" onclick="return false;" class="st_explaination">view</a></td>' +
		'</tr>';
	if( Template.site == 'shsat' || Template.site == 'ssat' ) Template.ScoreTestResult =
		'<tr>' +
			'<td class="st_question"></td>' +
			'<td class="st_questionType"></td>' +
			'<td class="st_yourAnswer"></td>' +
			'<td class="st_correctAnswer"></td>' +
		'</tr>';
		
	Template.ScoreTestBreakdown =
		'<table class="st_resultsTable">' +
			'<tr><th># of questions: </th><th class="st_breakdownNumberQuestions"></th></tr>' +
			'<tr><th># answered correctly: </th><th class="st_breakdownNumberAnswersCorrect"></th></tr>' +
			'<tr><th># answered incorrectly: </th><th class="st_breakdownNumberAnswersIncorrect"></th></tr>' +
			'<tr><th># answers omitted: </th><th class="st_breakdownNumberAnswersOmitted"></th></tr>' +
			'<tr><th>Score: </th><th class="st_breakdownPercentCorrect"></th></tr>' +
		'</table>';
	if( Template.site == 'shsat' || Template.site == 'ssat' ) 		Template.ScoreTestBreakdown =
		'<table class="st_resultsTable">' +
			'<tr><th># of questions: </th><th class="st_breakdownNumberQuestions"></th></tr>' +
			'<tr><th># answered correctly: </th><th class="st_breakdownNumberAnswersCorrect"></th></tr>' +
			'<tr><th># answered incorrectly: </th><th class="st_breakdownNumberAnswersIncorrect"></th></tr>' +
			'<tr><th># answers omitted: </th><th class="st_breakdownNumberAnswersOmitted"></th></tr>' +
			'<tr><th>Percent Correct: </th><th class="st_breakdownPercentCorrect"></th></tr>' +
		'</table>';
	
	Template.ScoreTestBreakdownCumulative = 
		'<table class="st_resultsTable">' +
			'<tr><th># of questions: </th><th class="st_breakdownNumberQuestions"></th></tr>' +
			'<tr><th># answered correctly: </th><th class="st_breakdownNumberAnswersCorrect"></th></tr>' +
			'<tr><th># answered incorrectly: </th><th class="st_breakdownNumberAnswersIncorrect"></th></tr>' +
			'<tr><th># answers omitted: </th><th class="st_breakdownNumberAnswersOmitted"></th></tr>' +
			'<tr><th>Score: </th><th class="st_breakdownPercentCorrect"></th></tr>' +
			'<tr><th>Stanine Analysis: </th><th class="st_breakdownAnalysis"></th></tr>' +
		'</table>';
	if( Template.site == 'shsat' || Template.site == 'ssat' ) 	Template.ScoreTestBreakdownCumulative = 
		'<table class="st_resultsTable">' +
			'<tr><th># of questions: </th><th class="st_breakdownNumberQuestions"></th></tr>' +
			'<tr><th># answered correctly: </th><th class="st_breakdownNumberAnswersCorrect"></th></tr>' +
			'<tr><th># answered incorrectly: </th><th class="st_breakdownNumberAnswersIncorrect"></th></tr>' +
			'<tr><th># answers omitted: </th><th class="st_breakdownNumberAnswersOmitted"></th></tr>' +
			'<tr><th>Percent Correct: </th><th class="st_breakdownPercentCorrect"></th></tr>' +
			'<tr><th>Score: </th><th class="st_breakdownAnalysis"></th></tr>' +
		'</table>';
	
	Template.ScoreTestAnswer = '<label class="st_answerText"></label>';
	Template.ScoreTestAnswerChoice = '<input type="radio" class="st_answerChoice" />';
	
	
	
	//************************************************************************************************
}) (jQuery);