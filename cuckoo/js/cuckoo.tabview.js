(function() {

  var cuckooTabView = OCA.Files.DetailTabView.extend({

    id: 'cuckooTabView',
    className: 'tab cuckooTabView',

    /**
     * get label of tab
     */
    getLabel: function() {

      return t('cuckoo', 'Cuckoo');

    },

    getIcon: function() {
       return 'icon-category-tools';
    },



    /**
     * Renders this details view
     *
     * @abstract
     */
    render: function() {
      this._renderSelectList(this.$el);

      this.delegateEvents({
        'click #cuckoo-send': '_onPressEvent'
      });

    },

    _renderSelectList: function($el) {
	finfo = this.getFileInfo();
	mtype = finfo.get('mimetype');
        $el.html('<strong>MimeType</strong>: '+mtype+'<br><div class="get-md5"></div><br><div align="center"><button id="cuckoo-send">Send to Cuckoo</button></div><br><div class="cuckoo-out"></div>');
	this.check2(finfo,'md5')
    },

    /**
     * show tab only on files
     */
    canDisplay: function(fileInfo) {

      if(fileInfo != null) {
        if(!fileInfo.isDirectory()) {
          return true;
        }
      }
      return false;

    },

    /**
     * ajax callback for generating md5 hash
     */
    check: function(fileInfo, algorithmType) {
      // skip call if fileInfo is null
      if(null == fileInfo) {
        _self.updateDisplay({
          response: 'error',
          msg: t('cuckoo', 'No fileinfo provided.')
        });

        return;
      }

      var url = OC.generateUrl('/apps/cuckoo/check'),
          data = {source: fileInfo.getFullPath(), type: algorithmType},
          _self = this;
      $.ajax({
        type: 'GET',
        url: url,
        dataType: 'json',
        data: data,
        async: true,
        success: function(data) {
	
          _self.updateDisplay(data, algorithmType);
        }
      });

    },

    send: function(fileInfo) {
	 this.$el.find('#cuckoo-send').html('');
	 this.$el.html('<div style="text-align:center; word-wrap:break-word; padding: 14px 28px;"><p><img src="'
          + OC.imagePath('core','loading.gif')
          + '"><br><br></p><p>'
          + t('cuckoo', 'Creating cuckoo ...')
          + '</p></div>');
	
      var url = OC.generateUrl('/apps/cuckoo/send'),
          data = {source: fileInfo.getFullPath()},
          _self = this;
      $.ajax({
        type: 'GET',
        url: url,
        dataType: 'json',
        data: data,
        async: true,
        success: function(data) {
		var element = document.createElement('a');
	  	element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(data));
	  	element.setAttribute('download', 'Raport.html');
	  	element.style.display = 'none';
	  	document.body.appendChild(element);
		
	  	element.click();

	  	document.body.removeChild(element);
		console.log(data)
		_self.updateDisplay(data);
	 	
        }
      });

    },

    updateDisplay: function(data) {
	   
	this.$el.html('<div style="text-align:left; word-wrap:break-word;">'+data+'</div>');


	
    },

    check2: function(fileInfo, algorithmType) {
      // skip call if fileInfo is null
      if(null == fileInfo) {
        _self.updateDisplay({
          response: 'error',
          msg: t('cuckoo', 'No fileinfo provided.')
        });

        return;
      }

      var url = OC.generateUrl('/apps/cuckoo/check'),
          data = {source: fileInfo.getFullPath(), type: algorithmType},
          _self = this;
      $.ajax({
        type: 'GET',
        url: url,
        dataType: 'json',
        data: data,
        async: true,
        success: function(data) {
	
          _self.updateDisplay2(data, algorithmType);
        }
      });

    },

    /**
     * display message from ajax callback
     */

    updateDisplay2: function(data, algorithmType) {
      var msg = '';
      
      if('success' == data.response) {
        msg = '<strong>'+algorithmType+'</strong>' + ': ' + data.msg;
      }
      if('error' == data.response) {
        msg = data.msg;
      }
	

      this.$el.find('.get-'+algorithmType).html(msg);

    },

    /**
     * changeHandler
     */
    _onPressEvent: function(ev) {
        this.send(this.getFileInfo());
    },

    _onChangeEvent: function(ev) {

      var algorithmType = $(ev.currentTarget).val();
	
      if(algorithmType != '') {
        this.$el.html('<div style="text-align:center; word-wrap:break-word;" class="get-cuckoo"><p><img src="'
          + OC.imagePath('core','loading.gif')
          + '"><br><br></p><p>'
          + t('cuckoo', 'Creating cuckoo ...')
          + '</p></div>');
        this.check(this.getFileInfo(), algorithmType);
      }
    },

    _onReloadEvent: function(ev) {
      ev.preventDefault();
      this._renderSelectList(this.$el);
      this.delegateEvents({
        'change #cuckoo-send': '_onPressEvent'
      });
    }

  });

  OCA.cuckoo = OCA.cuckoo || {};

  OCA.cuckoo.cuckooTabView = cuckooTabView;

})();
