import { Component, ViewChild } from '@angular/core';
import { IonicPage, NavController, NavParams, Content } from 'ionic-angular';
import { MessagesProvider, Message, Conversation } from '../../providers/messages/messages';
import { AuthProvider } from '../../providers/auth/auth';

// @IonicPage()
@Component({
  selector: 'show-page-message',
  templateUrl: 'show.html',
})
export class ShowMessagePage {
  item: any;
  reachedEnd: boolean = false;
  draft: any;
  @ViewChild(Content) content: Content;

  constructor(public navCtrl: NavController, public navParams: NavParams, private msg: MessagesProvider, private auth: AuthProvider) {
  	this.item = navParams.get('item');
  	this.draft = {
  		text: '',
  		conversation_id: this.item.id,
  		user: this.auth.currentUser,
  		other_id: this.item.other.id
	};
  	
  	if(this.item.messages.length < 10)
		this.reachedEnd = true;
  }

  sendMessage(e){
  	// e.stopPropagation();
  	this.msg.post(this.draft).subscribe(
  	  success => {
  	  	this.item.messages.push({...this.draft});
  	  	this.draft.text = '';
  	  	this.content.scrollToBottom();
  	  },
  	  error => console.log(error)
  	);
  }

  doInfinite (): Promise<any> {
  	  return new Promise((resolve) => {
  	  	setTimeout(() => {
		  let offset = this.item.messages.length;
		  this.msg.getMessages(this.item.id, offset)
		  	.subscribe(
		  		items => {
		  			this.item.messages = items.concat(this.item.messages);
		  			if(items.length < 10)
		  				this.reachedEnd = true;
		  			resolve();
		  		},
		  		error => console.log(<any>error),
		  		() => {
		  			// this.loading = false;
		  			// e.complete();
		  		}
		  	);
		  }, 500);
	  });
  }

}
