

jQuery(document).ready(function() {
  var correct=0;
  var wrong=0;
  var quizposts = new Object();
  var post1 = new Object();
  var url=jQuery("#url").data('url');
  var notattempted=0;
  var totQuestions=0;
  var resp=new Array();
  var quizid=jQuery("#quizid").data('quizid');
  jQuery.getJSON(url+'/wp-json/wp/v2/quiz/'+quizid,function(post){
            quizposts=post;
            totQuestions=Object.keys(post.questions).length;
            jQuery( ".quizTitle" ).text(post.title.rendered);
            jQuery( ".quizInfo" ).text(post.content);
            jQuery( ".totalQuestions" ).text('Total questions : '+totQuestions);
            i=0;
            while(i<totQuestions)
              {
                resp[i]=new Array();
                resp[i][0]=0;
                resp[i][1]=0;
                i++;
              }

              buildProgress(totQuestions);

          });

//jQuery("#close").confirm();

  jQuery("#close").click(function() {
          if(confirm("Are you sure you want to exit this quiz?")){
          document.getElementById("quiz").style.display = "none";
          jQuery("#startQuizBtn").removeClass("nav");
        } });

         jQuery("#startQuizBtn").click(function() {
          document.getElementById("quiz").style.display = "block";
          //jQuery(".progress").removeClass("hide");
            jQuery(".totalQuestions").addClass("nav");
            jQuery(".quizTitle").addClass("nav");
            jQuery(".quizInfo").addClass("nav");
            jQuery("#startQuizBtn").addClass("nav");
            jQuery("#next").removeClass("nav");
            jQuery(".option").removeClass("nav");
            jQuery("#prev").removeClass("nav");
            var questionid=jQuery("#questionid").text();
            getQuestion(quizid,questionid);
          });

         function afterQuizOver(){
          jQuery(".insideQuest").removeClass("hide");
          jQuery(".option1").removeClass("correct incorrect");
          jQuery(".option2").removeClass("correct incorrect");
          jQuery(".option3").removeClass("correct incorrect");
          jQuery(".option4").removeClass("correct incorrect");
          // jQuery(".insideQuest").html(`
          //       <ul id="result">
          //       <li class="resultSections">Your score ${correct}/${totQuestions}</li>
          //       <li class="resultSections">Correct : ${correct}</li>
          //       <li class="resultSections">InCorrect : ${wrong}</li>
          //       <li class="resultSections">Unattempted : ${notattempted}<br></li>
          //       </ul>
          //       <div id="afterquiz">
          //       <div class="question"></div>
          //       <div class="option1 option"></div>
          //       <div class="option2 option"></div>
          //       <div class="option3 option"></div>
          //       <div class="option4 option"></div>
          //       </div>

          //     `);

         }

         jQuery(document).on('click', '.wrongflag', function (e) {
          afterQuizOver();
          var viewAnsID=(this.innerHTML)-1;
        getQuestion(quizid,viewAnsID);
        flagwrong(viewAnsID);
        flagcorrect(viewAnsID);
    e.preventDefault();
});

         jQuery(document).on('click', '.correctflag', function (e) {
          afterQuizOver();
          var viewAnsID=(this.innerHTML)-1;
          getQuestion(quizid,viewAnsID);
          flagcorrect(viewAnsID);
          e.preventDefault();
});

         jQuery(document).on('click', '.holderUnattempted', function (e) {
          afterQuizOver();
          var viewAnsID=(this.innerHTML)-1;

jQuery.ajax({
   url:getQuestion(quizid,viewAnsID),
   success:function(){
   flagcorrect(viewAnsID);
}
})

         // getQuestion(quizid,viewAnsID);
        //flagcorrect(viewAnsID);



    e.preventDefault();

});






         jQuery(document).on('click', '.holder', function (e) {

          var viewAnsID=(this.innerHTML)-1;

        getQuestion(quizid,viewAnsID);



    e.preventDefault();
});
         jQuery(document).on('click', '.holderupdated', function (e) {

            var viewAnsID=(this.innerHTML)-1;
            getQuestion(quizid,viewAnsID);
            e.preventDefault();
});


         function flagwrong(id){
            if(resp[id][0]==1)
            jQuery(".option1").addClass("incorrect");
            else if(resp[id][0]==2)
            jQuery(".option2").addClass("incorrect");
            else if(resp[id][0]==3)
            jQuery(".option3").addClass("incorrect");
            else if(resp[id][0]==4)
            jQuery(".option4").addClass("incorrect");

         }
         function flagcorrect(id){

            if(resp[id][1]==1)
            jQuery(".option1").addClass("correct");
            else if(resp[id][1]==2)
            jQuery(".option2").addClass("correct");
            else if(resp[id][1]==3)
            jQuery(".option3").addClass("correct");
            else if(resp[id][1]==4)
            jQuery(".option4").addClass("correct");

         }


         jQuery("#next").click(function() {

            var questionid=jQuery("#questionid").text();
            var tempqid=parseInt(questionid);
            tempqid=tempqid+1;
            questionid=tempqid;
            getQuestion(quizid,questionid);


          });

         jQuery("#prev").click(function() {

            var questionid=jQuery("#questionid").text();
            var tempqid=parseInt(questionid);
            tempqid=tempqid-1;
            questionid=tempqid;
            getQuestion(quizid,questionid);


          });

         jQuery(".option1").click(function() {

            var questionid=jQuery("#questionid").text();
            var tempqid=parseInt(questionid);
            resp[tempqid][0]=1;
            setAttemptFlag(1);


          });
         jQuery(".option2").click(function() {

            var questionid=jQuery("#questionid").text();
            var tempqid=parseInt(questionid);
            resp[tempqid][0]=2;
            setAttemptFlag(2);


          });
         jQuery(".option3").click(function() {

            var questionid=jQuery("#questionid").text();
            var tempqid=parseInt(questionid);
            resp[tempqid][0]=3;
            setAttemptFlag(3);


          });
         jQuery(".option4").click(function() {

            var questionid=jQuery("#questionid").text();
            var tempqid=parseInt(questionid);
            resp[tempqid][0]=4;
            setAttemptFlag(4);


          });
//show hide navigator

jQuery(document).on('click', '.showprogress', function (e) {
            jQuery(".progress").removeClass("hide");
            jQuery(".navigator").addClass("hideprogress");
            jQuery(".navigator").removeClass("showprogress")

e.preventDefault();
          });



          jQuery(document).on('click', '.hideprogress', function (e) {

            jQuery(".progress").addClass("hide");
            jQuery(".navigator").addClass("showprogress");
            jQuery(".navigator").removeClass("hideprogress");
            e.preventDefault();
          });

         // jQuery(".hideprogress").click(function() {

         //    jQuery(".progress").addClass("hide");
         //    jQuery(".navigator").addClass("showprogress");
         //    jQuery(".navigator").removeClass("hideprogress");


         //  });

//after finishing the quiz result and solution navigation should be present
         jQuery("#finish").click(function() {


            i=0;
            while(i<totQuestions)

              {


                if(resp[i][0]==resp[i][1]&&resp[i][1]!=0)
              {
                correct=correct+1;
                jQuery("#q"+i).addClass("correctflag");
                jQuery("#q"+i).removeClass("holderupdated");
              }
              else
              if(resp[i][0]==0)
              {
                notattempted=notattempted+1;
              jQuery("#q"+i).addClass("holderUnattempted");
              jQuery("#q"+i).removeClass("holder");
            }

              else
                {
                  wrong=wrong+1;
                  jQuery("#q"+i).addClass("wrongflag");
                  jQuery("#q"+i).removeClass("holderupdated");
                }
              i++;
            }
            jQuery(".insideQuest").html(`

                <h1>Your have scored ${correct} out of ${totQuestions}</h1>
                <ul id="result">
                <li class="resultSections resultSections_correct">Correct<span class="scoreDisplay">${correct}</span></li>
                <li class="resultSections resultSections_incorrect">InCorrect<span class="scoreDisplay">${wrong}</span></li>
                <li class="resultSections resultSections_notattempted">Skipped<span class="scoreDisplay">${notattempted}</span><br></li>
                </ul>
                <div class="chart-container">
                <canvas id="myChart" ></canvas>
                </div>

              `);
            var ctx = document.getElementById("myChart");
            var myChart = new Chart(ctx, {
  type: 'pie',
  data: {
    labels: ['correct','incorrect','skipped'],

    datasets: [
      {
        data: [correct,wrong,notattempted],
        backgroundColor: [
          "green",
          "red",
          "grey"
        ]
      }
    ]
  }
});
            jQuery("#qno").text("");
            jQuery(".progress").addClass("hide");
            jQuery(".navigator").addClass("hide");
            jQuery(".final").removeClass("hide");
            document.getElementById("next").style.display = "none";
            document.getElementById("prev").style.display = "none";
            document.getElementById("finish").style.display = "none";
            //creating new attempt post via json
            i=0;
            var attemptLog='';
            while(i<totQuestions){
              attemptLog+=resp[i][0]+'|'+resp[i][1]+',';
              i++;
            }

        jQuery.ajax({
            method: "POST",
            url: POST_SUBMITTER.root + 'qnotsAttempts/v1/qnots_attempts',
            data : {
              'quiz_id':quizid,
              'attempts_log':attemptLog,
              'score':correct},
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', POST_SUBMITTER.nonce );
            },
            success : function( response ) {
                //console.log( response );
                alert( POST_SUBMITTER.success );
            },
            fail : function( response ) {
                //console.log( response );
                alert( POST_SUBMITTER.failure );
            }

        });

          });

          jQuery("#finalSectionSolutions").click(function() {
            jQuery("#qno").html('<div class="solutionInstruction">Click on a question to view solution.</div>');
            jQuery("#finalSectionResult").removeClass("active");
            jQuery("#finalSectionLeaderBoard").removeClass("active");
            jQuery("#finalSectionSolutions").addClass("active");
            jQuery(".progress").removeClass("hide");


            jQuery(".insideQuest").html(`


                <div id="afterquiz">
                <div class="question"></div>
                <div class="option1 option"></div>
                <div class="option2 option"></div>
                <div class="option3 option"></div>
                <div class="option4 option"></div>
                </div>

              `);
            jQuery(".insideQuest").addClass("hide");

          });
          jQuery("#finalSectionResult").click(function() {
            jQuery("#qno").text("");
            jQuery(".progress").addClass("hide");
            jQuery("#finalSectionSolutions").removeClass("active");
            jQuery("#finalSectionLeaderBoard").removeClass("active");
            jQuery("#finalSectionResult").addClass("active");
            jQuery(".insideQuest").removeClass("hide");
            jQuery(".insideQuest").html(`

                <h1>Your have scored ${correct} out of ${totQuestions}</h1>
                <ul id="result">
                <li class="resultSections resultSections_correct">Correct<span class="scoreDisplay">${correct}</span></li>
                <li class="resultSections resultSections_incorrect">InCorrect<span class="scoreDisplay">${wrong}</span></li>
                <li class="resultSections resultSections_notattempted">Skipped<span class="scoreDisplay">${notattempted}</span><br></li>
                </ul>
                <div class="chart-container">
                <canvas id="myChart" ></canvas>
                </div>

              `);
            var ctx = document.getElementById("myChart");
            var myChart = new Chart(ctx, {
  type: 'pie',
  data: {
    labels: ['correct','incorrect','skipped'],

    datasets: [
      {
        data: [correct,wrong,notattempted],
        backgroundColor: [
          "green",
          "red",
          "grey"
        ]
      }
    ]
  }
});


          });


          //Leaderboard section
          jQuery("#finalSectionLeaderBoard").click(function() {
            jQuery("#qno").text("");
            jQuery(".progress").addClass("hide");
            jQuery("#finalSectionResult").removeClass("active");
            jQuery("#finalSectionSolutions").removeClass("active");
            jQuery("#finalSectionLeaderBoard").addClass("active");
            jQuery(".insideQuest").removeClass("hide");
            //jQuery.getJSON(url+'/wp-json/wp/v2/qnots_attempts/?filter[orderby]=meta_value_num&filter[meta_key]=score&filter[order]=desc&filter[meta_key]=quiz_id&filter[meta_value]='+quizid,function(attempts){
              //for(i = 0; i < Object.keys(attempts).length; i++) {
              // attempts.sort(function(a, b){
              //   return b.score - a.score;
              // });
            jQuery.getJSON(url+'/wp-json/qnotsAttempts/v1/qnots_attempts/?quiz_id='+quizid,function(attempts){
            var attemptsData='<div>Top 10 scorers</div><br><div><ul class="lbdisplayheader"><li>Rank</li><li>Name</li><li>Score</li></ul></div>';
              for(i = 0; i < Object.keys(attempts).length&&i<10; i++) {

                     attemptsData+='<div><ul class="lbdisplay"><li>'+(i+1)+'</li><li>'+attempts[i].user_name+'</li><li>'+attempts[i].score+'</li></ul></div>';
                  }

              jQuery(".insideQuest").html(`
                ${attemptsData}

              `);

              });
          });


          //sets the attempt flag
         function setAttemptFlag(flag){
          jQuery(".option1").removeClass("attempted");
          jQuery(".option2").removeClass("attempted");
          jQuery(".option3").removeClass("attempted");
          jQuery(".option4").removeClass("attempted");
          if(flag==1)
            jQuery(".option1").addClass("attempted");
          else if(flag==2)
            jQuery(".option2").addClass("attempted");
          else if(flag==3)
            jQuery(".option3").addClass("attempted");
          else if(flag==4)
            jQuery(".option4").addClass("attempted");

          var questionid=jQuery("#questionid").text();
          var tempqid=parseInt(questionid);
          jQuery("#q"+tempqid).removeClass("holder");
          jQuery("#q"+tempqid).addClass("holderupdated");

         }
         //gets the attempt flag
         function getAttemptFlag(flag){
          jQuery(".option1").removeClass("attempted");
          jQuery(".option2").removeClass("attempted");
          jQuery(".option3").removeClass("attempted");
          jQuery(".option4").removeClass("attempted");

          if(flag!=0)
          {


            if(flag==1)
            jQuery(".option1").addClass("attempted");
          else if(flag==2)
            jQuery(".option2").addClass("attempted");
          else if(flag==3)
            jQuery(".option3").addClass("attempted");
          else if(flag==4)
            jQuery(".option4").addClass("attempted");
        }

         }

//shows the navigation of questions
function buildProgress(qcount){
  i=0;
  var questHolder='';

  while(i<qcount)
       { questHolder+='<button class="progressBtn holder" id="q'+i+'">'+(i+1)+'</button>';
        i++; }
  jQuery(".progress").html(questHolder);
}


//function to fetch the question
function getQuestion(quizid,id){

if((totQuestions-1)==id){
  jQuery("#next").addClass("nav");

  jQuery("#finish").removeClass("nav");
}
else
{
jQuery("#next").removeClass("nav");

}
if(id==0){
  jQuery("#prev").addClass("nav");
}
else
 jQuery("#prev").removeClass("nav");



jQuery.getJSON(url+'/wp-json/wp/v2/questions/'+quizposts.questions[id],function(qpost){

              resp[id][1]=qpost.correctanswer;
              jQuery( "#qno" ).text((parseInt(id)+1)+' of '+totQuestions);
              jQuery( ".question" ).text(qpost.question);
              jQuery( ".option1" ).text(qpost.answerchoice1);
              jQuery( ".option2" ).text(qpost.answerchoice2);
              jQuery( ".option3" ).text(qpost.answerchoice3);
              jQuery( ".option4" ).text(qpost.answerchoice4);
              jQuery( ".hiddenID" ).text(id);
              getAttemptFlag(resp[id][0]);
});
}
});

