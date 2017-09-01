import { Component, ViewChild } from '@angular/core';
import { NavController, NavParams, Content } from 'ionic-angular';
import { MessagesProvider, Conversation } from '../../providers/messages/messages';
import { AuthProvider } from '../../providers/auth/auth';

@Component({
  selector: 'show-page-message',
  templateUrl: 'show.html',
})
export class ShowMessagePage {
  item: Conversation;
  founder: boolean = false;
  remindAnonymous: boolean = false;
  reachedEnd: boolean = false;
  draft: any = {
    text: '',
    user: null,
    conversation_id: null,
    other_id: null,
    card_id: null
  };
  loading: boolean = false;
  limit: number = 10;
  watchForNew: boolean = true;
  @ViewChild(Content) content: Content;

  constructor(public navCtrl: NavController, public navParams: NavParams, private msg: MessagesProvider, private auth: AuthProvider) {}

  ngOnInit(){
  	this.item = this.navParams.get('item');
    
    if(!this.item.id){
      this.loading = true;
      this.msg.getConversations(this.auth.currentUser.id, this.item.other.id, this.item.card ? this.item.card.id : null, 0, this.limit)
        .subscribe(
          items => {
            this.item = items[0];
            this.safeToInit();
          },
          error => console.log(<any>error),
          () => {
            this.loading = false;
          }
        );
    }
    else
      this.safeToInit();
  }

  safeToInit(){
    // determine anonymity
    if(this.item.card){
      this.founder = this.item.card.founder_id == this.auth.currentUser.id;
      if(this.founder)
        this.remindAnonymous = this.item.messages.filter(item => item.user.id === this.auth.currentUser.id).length === 0;
    }
    // update draft
    this.draft.conversation_id = this.item.id,
    this.draft.user = this.auth.currentUser;
    this.draft.other_id = this.item.other.id;
    this.draft.card_id = this.item.card ? this.item.card.id : 0;
    
    this.reachedEnd = this.item.messages.length < this.limit;
    this.watchForNewMessages();
  }

  ionViewDidEnter() {
    this.watchForNew = true;
    setTimeout(() => {
      if(this.content) this.content.scrollToBottom();
    }, 500);
  }

  ionViewWillLeave() {
    this.watchForNew = false;
  }

  watchForNewMessages(){
    let lastMsgID = this.item.messages.length ? this.item.messages[this.item.messages.length-1].id : null;
    this.msg.getNewMessages( this.item.id, this.auth.currentUser.id, lastMsgID )
      .subscribe(
        items => {
          this.item.messages = this.item.messages.concat(items);
          if(items.length)
            setTimeout(() => {
              if(this.content) this.content.scrollToBottom();
              this.msg.markAsRead(this.auth.currentUser.id, this.item.id).subscribe(
                  items => console.log('auto-marked-as-read: ', items),
                  error => console.log(<any>error)
                  // make sure in app and os badge's aren't incremented
              );
            }, 500);
          setTimeout(() => { 
            if(this.watchForNew)
              this.watchForNewMessages();
          }, 5000)
        },
        error => console.log(<any>error)
      );
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
      if(this.content)
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
