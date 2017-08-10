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
  loading: boolean = false;
  @ViewChild(Content) content: Content;

  constructor(public navCtrl: NavController, public navParams: NavParams, private msg: MessagesProvider, private auth: AuthProvider) {
  	this.item = navParams.get('item');
    
    if(!this.item.id){
      this.loading = true;
      this.msg.getConversations(this.auth.currentUser.id, this.item.other.id, this.item.card_id)
        .subscribe(
          items => {
            this.item = items[0];
            this.reachedEnd = this.item.messages.length < 10;
          },
          error => console.log(<any>error),
          () => {
            this.loading = false;
          }
        );
    }

  	this.draft = {
  		text: '',
  		conversation_id: this.item.id,
  		user: this.auth.currentUser,
  		other_id: this.item.other ? this.item.other.id : this.item.other_id,
      card_id: this.item.card_id || 0
	};
  	
    this.reachedEnd = this.item.messages.length < 10;
  }

  ionViewDidEnter() {
    setTimeout(() => {
      this.content.scrollToBottom();
    }, 500);
  }

  sendMessage(e){
  	// e.stopPropagation();
  	this.msg.post(this.draft).subscribe(
  	  success => {
        if(!this.draft.conversation_id)
          this.draft.conversation_id = success.conversation_id;
  	  	this.item.messages.push({...this.draft});
  	  	this.draft.text = '';
  	  	setTimeout(() => {
          this.content.scrollToBottom();
        }, 500);
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
                this.reachedEnd = items.length < 10;
    		  			resolve();
    		  		},
    		  		error => console.log(<any>error)
    		  	);
    		  }, 500);
	  });
  }

}
