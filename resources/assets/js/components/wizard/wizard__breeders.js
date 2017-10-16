const emptyBreeder = App.emptyBreeder;
emptyBreeder.aquired = null;

 App.Components.ImageUploadRow = App.Components.ImageUpload.extend({
     template: '#image-upload-row-template',

     methods: {
         initUploader: function () {
             var self = this;
             $(this.$els.image).unsigned_cloudinary_upload(this.cloud_settings_name,
                 { cloud_name: App.cloud_name},
                 { multiple: false }
             ).bind('fileuploadstart', function(e, data) {

                 self.loading = 1;
             }).bind('cloudinarydone', function(e, data) {
                 self.loading = 0;
                 self.breeder.image = Object.assign({}, {
                     name : data.result.public_id,
                     path : data.result.url,
                     temp : false,
                     oldImage: self.breeder.image.oldImage,
                     delete: self.breeder.image.delete,
                 });
                 console.log('done', self.breeder.name);
                 //self.$broadcast('image-uploaded', data.result.image);
             }).bind('fileuploadfail', function(e, data) {
                 self.loading = 0;
             });
         },
     }
 });


const WizardBreeder = {
    template: "#wizard__breeder-template",
    data: function () {
        return {

        }
    },
    props: ['breeder', 'fields', 'user', 'autocomplete'],
    components: {
        'lbs-oz-input': App.Components.LbsOzInput,
        'image-upload': App.Components.ImageUploadRow,
    },
    methods: {
        setSex(sex){
            this.breeder.sex = sex;
        },
        deleteEntry: function deleteEntry() {
            // this.breeder = null;
            // this.$remove();
            this.$remove();
            this.$parent.breeders.$remove(this.breeder);
        },

        initTypeahead: function() {

            for(let field in this.fields) {
                $(this.$els[field]).typeahead({
                    hint: true,
                    highlight: true,
                    minLength: 0
                }, {
                    source: (req, callback) => {
                        callback(this.autocomplete[field].filter(function (name) {
                            return name.toLowerCase().indexOf(req.toLowerCase()) !== -1;
                        }));
                    }
                });
            }

        },
    },
    ready: function () {
        this.initTypeahead();
    }
};

const WizardBreederForm = {
    template: "#wizard__breeder-form-template",
    data: function () {
        return {
            initialized: false
        }
    },
    props: ['breeder', 'user', 'fields', 'autocomplete', 'current'],
    components: {
        'image-upload': App.Components.ImageUpload,
        'sex-select': App.Components.SexSelect,
        'lbs-oz-input': App.Components.LbsOzInput
    },

    methods: {
        initTypeahead: function() {
            for(let field in this.autocomplete) {
                $(this.$els[field]).typeahead({
                    hint: true,
                    highlight: true,
                    minLength: 0
                }, {
                    source: (req, callback) => {
                        callback(this.autocomplete[field].filter(function (name) {
                            return name.toLowerCase().indexOf(req.toLowerCase()) !== -1;
                        }));
                    }
                });
            }
        },
    },

    ready: function () {
        this.initTypeahead();
    }
};

App.Components.WizardBreeders = {
    template: "#wizard__breeders-template",
    data: function () {
        return {
            user: {},
            breedersNumber: 0,
            fields: {

            },
            mode: null,
            breeders: [],
            current: {},
            loading: false,
            autocomplete: {},
            loadingEvans: false,
            loadingCsv: false,
        }
    },
    props: [],
    components: {
        'settings-image-upload': App.Components.SettingsImageUpload,
        'breeder' : WizardBreeder,
        'breeder-form': WizardBreederForm,

    },
    computed: {
        isSubscribed: function() {
            return App.isSubscribed;
        },
		
		prefixes: function() {
            return this.processAutocompleteField('prefix');
        },
		
        names: function() {
            return this.processAutocompleteField('name');
        },

        tattoos: function() {
            return this.processAutocompleteField('tattoo');
        },

        breeds: function() {
            return this.processAutocompleteField('breed');
        },

        cages: function() {
            return this.processAutocompleteField('cage');
        },

        colors: function() {
            return this.processAutocompleteField('color');
        },


        autocomplete: function() {
            return {
                prefix: this.prefixes,
                name: this.names,
                tattoo: this.tattoos,
                cage: this.cages,
                breed: this.breeds,
                color: this.colors
            }
        },
    },
    watch:{
        breedersNumber(value, oldValue){
            if(value < oldValue){
                this.breeders = this.breeders.slice(0, value - 1);
            }

            if(value > 50){
                value = 50;
                this.breedersNumber = 50;
            } else {
                this.setBreedersRows(value);
            }
        }
    },
    methods: {
        addRow(){
            // let breeder = Object.assign({}, emptyBreeder);
            // this.breeders.push(breeder);
            this.breedersNumber +=1;
        },

        processAutocompleteField(field){
            return _.uniq(_.filter(_.pluck(this.breeders, field), (val) => val != ""));
        },

        setBreedersRows(value){
            while(this.breeders.length < value){
                let breeder = Object.assign({}, emptyBreeder);
                this.breeders.push(breeder);
            }
        },
        saveBreeders(){
            if(!this.loading){

                this.loading = true;

                api.saveBreeders(this.breeders, this.fields, emptyBreeder).then((response) => {
                    this.loading = false;
                    this.$router.go({ path: '/breeders' });
                }, response => {
                    console.log('error', response)
                    this.loading = false;
                });
            }
        },
        nextBreeder(){
            if(this.current < this.breedersNumber - 1){
                this.current +=1;
            } else {
                $('#wizard-breeder-modal').modal('hide');
            }
        },
        previousBreeder(){
            if(this.current > 0){
                this.current--;
            }
        },

        parseFile(e) {
          if (!this.loadingCsv || !this.loadingEvans) {
            var _this4 = this;
            if(e.target.id == 'import-file'){
              this.loadingCsv = true;
            }
            else if(e.target.id == 'import-html'){
              this.loadingEvans = true;
            }
            if (!$(e.target).val()) {
                return;
            }
            api.importFile(e.target.files[0]).then(function (data) {
                var _iteratorNormalCompletion = true;
                var _didIteratorError = false;
                var _iteratorError = undefined;

                try {
                    for (var _iterator = data.breeders[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                        var breeder = _step.value;

                        breeder.aquired = breeder.acquired;
                        delete breeder.acquired;

                        var mapObj = {
                            Pedigree: "",
                            pedigree: ""
                        };
                        var breedRemovePedigree = breeder.breed.replace(/Pedigree|pedigree/gi, function (matched) {
                            return mapObj[matched];
                        });
                        breeder.breed = breedRemovePedigree;

                        for (var key in breeder) {
                            if (key != 'father_name' && key != 'mother_name') {
                                if (breeder.hasOwnProperty(key) && !breeder[key]) {
                                    delete breeder[key];
                                }
                            }
                        }

                        _this4.breeders.push(Object.assign({}, emptyBreeder, breeder));
                        _this4.breedersNumber += 1;
                        if(e.target.id == 'import-file'){
                          _this4.loadingCsv = false;
                        }
                        else if(e.target.id == 'import-html'){
                          _this4.loadingEvans = false;
                        }
                    }
                } catch (err) {
                    _didIteratorError = true;
                    _iteratorError = err;
                    if(e.target.id == 'import-file'){
                      _this4.loadingCsv = false;
                    }
                    else if(e.target.id == 'import-html'){
                      _this4.loadingEvans = false;
                    }
                } finally {
                    try {
                        if (!_iteratorNormalCompletion && _iterator.return) {
                            _iterator.return();
                        }
                    } finally {
                        if (_didIteratorError) {
                            throw _iteratorError;
                        }
                    }
                    if(e.target.id == 'import-file'){
                      _this4.loadingCsv = false;
                    }
                    else if(e.target.id == 'import-html'){
                      _this4.loadingEvans = false;
                    }
                }

                _this4.mode = 'import';
            }, function (errors) {
                _this4.importErrors = errors.data;
                if(e.target.id == 'import-file'){
                  _this4.loadingCsv = false;
                }
                else if(e.target.id == 'import-html'){
                  _this4.loadingEvans = false;
                }
            });
          }
        },
        beginManual() {
            this.breedersNumber = 2;
            this.mode = 'manual';
        },
    },

    ready: function () {

        api.getCurrentUserSettingsData().then(data => {
            this.user = data.user;
            this.setBreedersRows(this.breedersNumber);

            for(let field in emptyBreeder){
                if(emptyBreeder.hasOwnProperty(field)){
                    this.fields[field] = true;
                    this.fields = Object.assign({}, this.fields);

                    this.autocomplete[field] = [];
                    this.autocomplete = Object.assign({}, this.autocomplete);
                }

            }
            this.current = 0;
        });



    },
    route: {
        activate: function () {
            window.scrollTo(0, 0);
        },
    }
};

