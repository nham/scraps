$(function(){

  Backbone.emulateHTTP = true;
  Backbone.emulateJSON = true;

  window.Scrap = Backbone.Model.extend({

    clear: function() {
      this.destroy();
      this.view.remove();
    },

    validate: function(a) {
      if(!a.body) {
        return "scrap body cannot be empty";
      }
    }

  });


  // The collection of scraps.
  window.Scrapyard = Backbone.Collection.extend({
    model: Scrap,
    url: "scrapyard/",

    comparator: function(scrap) {
      return scrap.get('created');
    }

  });

  // Create our global collection of **Scraps**.
  window.Scraps = new Scrapyard;


  // The DOM element for a scrap
  window.ScrapView = Backbone.View.extend({

    tagName:  "li",
    className: "scrap",

    // Cache the template function for a single item.
    template: _.template($('#item-template').html()),

    events: {
      "dblclick div.scrap-body": "edit",
      "dblclick span.scrap-date": "edit",
      "click span.scrap-destroy": "clear",
    },


    initialize: function() {
      _.bindAll(this, 'render', 'close');
      this.model.bind('change', this.render);
      this.model.view = this;
    },

    render: function() {
      $(this.el).html(this.template(this.model.toJSON()));
      this.setContent();
      return this;
    },

    setContent: function() {
      var body = this.model.get('body');
      var created = new Date(this.model.get('created') * 1000);
      var converter = new Showdown.converter();

      this.$('.scrap-body').html(converter.makeHtml(body));
      this.$('.scrap-date').text(created.format('d M Y g:i a'));
      this.input = this.$('.scrap-input');
      this.input.bind('blur', this.close);
      this.input.val(body);
    },


    edit: function() {
      $(this.el).addClass("editing");
      this.input.focus();
    },

    close: function() {
      this.model.save({body: this.input.val()});
      $(this.el).removeClass("editing");
    },

    // Remove this view from the DOM.
    remove: function() {
      $(this.el).remove();
    },

    // Remove the item, destroy the model.
    clear: function() {
      var confirm_delete = confirm("Please confirm that you want to delete this note.");
      
      if(confirm_delete == true) {
        this.model.clear();
      }
    }

  });


  // Top-level view
  window.AppView = Backbone.View.extend({
    el: $("#scrapapp"),

    events: {
      "click #create-new":  "createNew",
      "click #cancel-new": "cancelNew",
      "click #save-new":  "saveNew",
      "keyup #search-box": "instantSearch",
    },


    initialize: function() {
      _.bindAll(this, 'addOne', 'addAll', 'render');

      this.input = $("#new-scrap");
      this.search_input = $("#search-box");

      Scraps.bind('add',     this.addOne);
      Scraps.bind('refresh', this.addAll);
      Scraps.bind('all',     this.render);

      Scraps.fetch();
    },


    addOne: function(scrap) {
      var view = new ScrapView({model: scrap});
      $("#scrap-list").prepend(view.render().el);
    },

    // Add all items in the **Scraps** collection at once.
    addAll: function() {
      Scraps.each(this.addOne);
    },

    // Generate the attributes for a new Scrap item.
    newAttributes: function() {
      return {
        body: this.input.val(),
        created: Math.round(new Date().getTime() / 1000)
      };
    },

    saveNew: function() {
      Scraps.create(this.newAttributes());
      this.input.val('');
      this.cancelNew();
    },

    createNew: function() {
      $('#create-scrap').addClass("editing");
    },

    cancelNew: function() {
      $('#create-scrap').removeClass("editing");
    },

    instantSearch: function(event) {
      var searchstr = this.search_input.val().toLowerCase();
      var search_func;

      if(searchstr.length > 1) {
        search_func = function() {
          var scrap_el = $(this);
  
          if(scrap_el.text().toLowerCase().indexOf(searchstr) === -1) {
            scrap_el.hide();
	  } else {
            scrap_el.show();
	  }
	}
      } else {
        search_func = function() {
          $(this).show();
        }
      }

      $("#scrap-list").children().each(search_func);
    }

  });

  var validationResponse = function(data) {
    // Only display the notes  if the user is correctly logged in
    if(data === "true") {
      $('#scrapapp').addClass('auth');
      window.App = new AppView;
    } else {
      $('#login-msg').addClass('no-auth');
    }
  }

  if(document.cookie.indexOf("auth") !== -1) {
    var authnum = document.cookie.split('=')[1];
    var params = {
      url: 'auth/',
      type: 'GET',
      data: null,
      success: validationResponse
    }

    $.ajax(params);
  } else {
    $('#login-msg').addClass('no-auth');
  }

});