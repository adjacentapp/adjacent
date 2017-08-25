import { Component, ViewChild } from '@angular/core';
import { NavController, NavParams, Content } from 'ionic-angular';
import { MessagesProvider } from '../../providers/messages/messages';
import { AuthProvider } from '../../providers/auth/auth';

@Component({
  selector: 'show-page-message',
  templateUrl: 'show.html',
})
export class ShowMessagePage {
  item: any;
  founder: boolean = false;
  remindAnonymous: boolean = false;
  reachedEnd: boolean = false;
  draft: any;
  loading: boolean = false;
  limit: number = 10;
  @ViewChild(Content) content: Content;

  constructor(public navCtrl: NavController, public navParams: NavParams, private msg: MessagesProvider, private auth: AuthProvider) {
  	this.item = navParams.get('item');
    
    if(!this.item.id){
      this.loading = true;
      this.msg.getConversations(this.auth.currentUser.id, this.item.other.id, this.item.card_id, 0, this.limit)
        .subscribe(
          items => {
            this.item = items[0];
            this.reachedEnd = this.item.messages.length < this.limit;
            // determine anonymity
            if(this.item.card){
              this.founder = this.item.card.founder_id == this.auth.currentUser.id;
              if(this.founder)
                this.remindAnonymous = this.item.messages.filter(item => item.user.id === this.auth.currentUser.id).length === 0;
            }
          },
          error => console.log(<any>error),
          () => {
            this.loading = false;
          }
        );
    }
    else {
      // determine anonymity
      if(this.item.card){
        this.founder = this.item.card.founder_id == this.auth.currentUser.id;
        if(this.founder)
          this.remindAnonymous = this.item.messages.filter(item => item.user.id === this.auth.currentUser.id).length === 0;
      }
    }

  	this.draft = {
  		text: '',
  		conversation_id: this.item.id,
  		user: this.auth.currentUser,
  		other_id: this.item.other ? this.item.other.id : this.item.other_id,
      card_id: this.item.card_id || 0
	};
  	
    this.reachedEnd = this.item.messages.length < this.limit;

    setInterval(() => {
      this.msg.getNewMessages(this.item.id, this.item.other.id, this.item.messages.length ? this.item.messages[this.item.messages.length-1].id : null)
        .subscribe(
          items => {
            this.item.messages = this.item.messages.concat(items);
            if(items.length)
              setTimeout(() => {
                this.content.scrollToBottom();
                this.msg.markAsRead(this.auth.currentUser.id, this.item.id).subscribe(
                    items => console.log('auto-marked-as-read: ', items),
                    error => console.log(<any>error)
                    // make sure in app and os badge's aren't incremented
                );
              }, 500);
           },
          error => console.log(<any>error)
        );
    }, 5000);
  }

  ionViewDidEnter() {
    setTimeout(() => {
      this.content.scrollToBottom();
    }, 500);
  }

  sendMessage(e){
  	// e.stopPropagation();
    this.remindAnonymous = false;
  	this.msg.post(this.draft).subscribe(
  	  success => {
        if(!this.draft.conversation_id)
          this.draft.conversation_id = success.conversation_id;
        this.item.messages[this.item.messages.length-1].id = success.id
  	  },
  	  error => console.log(error)
  	);
    // dangerous optimism!!
    let newMsg = {...this.draft}
    newMsg.id = this.item.messages.length ? this.item.messages[this.item.messages.length-1].id + 1 : 1;
    this.item.messages.push(newMsg);
    this.draft.text = '';
    setTimeout(() => {
      this.content.scrollToBottom();
    }, 500);
  }

  doInfinite (): Promise<any> {
  	  return new Promise((resolve) => {
  	  	setTimeout(() => {
    		  let offset = this.item.messages.length;
    		  this.msg.getMessages(this.item.id, offset, this.limit)
    		  	.subscribe(
    		  		items => {
    		  			this.item.messages = items.concat(this.item.messages);
                this.reachedEnd = items.length < this.limit;
    		  			resolve();
    		  		},
    		  		error => console.log(<any>error)
    		  	);
    		  }, 500);
	  });
  }

}
