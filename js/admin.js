(function($) {
 
	var Quiz = { Views:{} };
    var wpq  = window.wpQuiz;
 	
	Quiz.Model = Backbone.Model.extend({
        defaults : {
            'correct' : false
        },
        url : ajaxurl+'?action=save_answer',
        toJSON : function() {
            var attrs = _.clone( this.attributes );
            attrs.post_id = wpq.post_id;
            return attrs;
        },
        initialize : function() {
            if ( this.get( 'answer_id' ) === wpq.answers.correct ) {
                this.set( 'correct', true );
            }
        }
    });
	
	Quiz.Collection = Backbone.Collection.extend({
		model: Quiz.Model
	});
	
	Quiz.Views.Inputs = Backbone.View.extend({
        initialize:function () {
            this.collection.each( this.addInput, this );
        },
        addInput : function( model, index ) {
            var input = new Quiz.Views.Input({ model:model });
            this.$el.append( input.render().el );
        }
    });
	
	Quiz.Views.Input = Backbone.View.extend({
        tagName: 'p',
		
        // Get the template from the DOM
        template :_.template( $(wpq.inputTempl).html() ),

        // When a model is saved, return the button to the disabled state
        initialize:function () {
            var _this = this;
            this.model.on( 'sync', function() {
                _this.$('button').text( 'Save' ).attr( 'disabled', true );
            });
        },

        // Attach events
        events : {
            'keyup input' : 'blur',
            'blur input' : 'blur',
            'click button' : 'save'
        },

        // Perform the Save
        save : function( e ) {
            e.preventDefault();
            $(e.target).text( 'wait' );
            this.model.save();
        },

        // Update the model attributes with data from the input field
        blur : function() {
            var input = this.$('input').val();
            if ( input !== this.model.get( 'answer' ) ) {
                this.model.set('answer', input);
                this.$('button').attr( 'disabled', false );
            }
        },

        // Render the single input - include an index.
        render:function () {
            this.model.set( 'index', this.model.collection.indexOf( this.model ) + 1 );
            this.$el.html( this.template( this.model.toJSON() ) );
            return this;
        }
    });
	
	Quiz.Views.Select = Backbone.View.extend({
        initialize:function () {
            this.collection.each( this.addOption, this );
        },
        addOption:function ( model ) {
            var option = new Quiz.Views.Option({ model:model });
            this.$el.append( option.render().el );
        }
    });
	
	Quiz.Views.Option = Backbone.View.extend({
        tagName:'option',

        // returning a hash allows us to set attributes dynamically
        attributes:function () {
            return {
                'value':this.model.get( 'answer_id' ),
                'selected':this.model.get( 'correct' )
            }
        },

        // Watch for changes to each model (that happen in the input fields and re-render when there is a change
        initialize:function () {
            this.model.on( 'change:answer', this.render, this );
        },
        render:function () {
            this.$el.text( this.model.get( 'answer' ) );
            return this;
        }
    });
	
	var answers = new Quiz.Collection( wpq.answers );
    var selectElem = new Quiz.Views.Select({ collection:answers, el:wpq.answerSelect });
    var inputs = new Quiz.Views.Inputs({ collection:answers, el:wpq.answerInput });
	
}(jQuery));