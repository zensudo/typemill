const app = Vue.createApp({
	template: `<Transition name="initial" appear>
	  					<form class="inline-block w-full">
							<p v-if="version.system !== undefined"><a href="https://typemill.net" class="block p-2 text-center bg-rose-500 text-white">Please update typemill to version {{ version.system }}</a></p>
							<ul class="flex mt-4 mb-4">
								<li v-for="tab in tabs" class="">
									<button class="px-2 py-2 border-b-2 border-stone-200 hover:border-stone-700 transition duration-100" :class="(tab == currentTab) ? 'border-stone-700' : ''" @click.prevent="activateTab(tab)">{{ $filters.translate(tab) }}</button>
								</li>
							</ul>
							<div v-for="(fieldDefinition, fieldname) in formDefinitions">
								<fieldset class="flex flex-wrap justify-between" :class="(fieldDefinition.legend == currentTab) ? 'block' : 'hidden'" v-if="fieldDefinition.type == 'fieldset'">
									<component v-for="(subfieldDefinition, fieldname) in fieldDefinition.fields"
										:key="fieldname"
										:is="selectComponent(subfieldDefinition.type)"
										:errors="errors"
										:name="fieldname"
										:userroles="userroles"
										:value="formData[fieldname]" 
										v-bind="subfieldDefinition">
									</component>
								</fieldset>
							</div>
							<div class="my-5">
								<div :class="messageClass" class="block w-full h-8 px-3 py-1 my-1 text-white transition duration-100">{{ $filters.translate(message) }}</div>
								<input type="submit" @click.prevent="save()" :value="$filters.translate('save')" class="w-full p-3 my-1 bg-stone-700 hover:bg-stone-900 text-white cursor-pointer transition duration-100">
							</div>
				  		</form>
			  		</Transition>`,
	data() {
		return {
			currentTab: 'System',
			tabs: [],
			formDefinitions: data.system,
			formData: data.settings,
			message: '',
			messageClass: '',
			errors: {},
			version: false,
		}
	},
	mounted() {

		eventBus.$on('forminput', formdata => {
			this.formData[formdata.name] = formdata.value;
		});

		for (var key in this.formDefinitions)
		{
			if (this.formDefinitions.hasOwnProperty(key))
			{
				this.tabs.push(this.formDefinitions[key].legend);
				this.errors[key] = false;
			}
		}

		var self = this;

		tmaxios.post('/api/v1/versioncheck',{
			'url':	data.urlinfo.route,
			'type': 'system',
			'data': this.formData.version
		})
		.then(function (response)
		{
			if(response.data.system)
			{
				self.version = response.data.system;
				console.info(self.version);
			}
		})
		.catch(function (error)
		{
			self.messageClass = 'bg-rose-500';
			self.message = error.response.data.message;
		});
	},
	methods: {
		selectComponent: function(type)
		{
			return 'component-'+type;
		},
		activateTab: function(tab){
			this.currentTab = tab;
			this.reset();
		},
		save: function()
		{
			this.reset();
			var self = this;

			tmaxios.post('/api/v1/settings',{
				'settings': this.formData
			})
			.then(function (response)
			{
				self.messageClass = 'bg-teal-500';
				self.message = response.data.message;
			})
			.catch(function (error)
			{
				self.messageClass = 'bg-rose-500';
				self.message = error.response.data.message;
				if(error.response.data.errors !== undefined)
				{
					self.errors = error.response.data.errors;
				}
			});			
		},
		reset: function()
		{
			this.errors 			= {};
			this.message 			= '';
			this.messageClass	= '';
		}
	},
})