import { Component, Input } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import { ProfilePage } from '../../pages/profile/profile';
import { ShowCardPage } from '../../pages/card/show';
import { WallProvider, Comment } from '../../providers/wall/wall';
import { AuthProvider } from '../../providers/auth/auth';
import { InAppBrowser } from '@ionic-native/in-app-browser';

@Component({
  selector: 'comment-component',
  templateUrl: 'comment-component.html'
})
export class CommentComponent {
  @Input() item: Comment;
  @Input() founder_id: number;
  @Input() showVotes: boolean = true;
  vote: number;
  founder: boolean = false;

  constructor(public navCtrl: NavController, public navParams: NavParams, private wall: WallProvider, private auth: AuthProvider, private iab: InAppBrowser) {}

  ngOnInit() {
    for (let liker_id of this.item.likes)
      if(liker_id == this.auth.currentUser.id)
        this.vote = 1;
    for (let disliker_id of this.item.dislikes)
      if(disliker_id == this.auth.currentUser.id)
        this.vote = -1;

    this.founder = this.founder_id == this.item.user.id;

    // this.item.message = urlify(this.item.message);
  }

  urlify(text) {
    var urlRegex = /(https?:\/\/[^\s]+)/g;
    return text.replace(urlRegex, function(url) {
        return '<a href="' + url + '">' + url + '</a>';
    });
  }

  checkForLink(text){
    let urlRegex = /(https?:\/\/[^\s]+)/g;
    console.log(urlRegex);
    // this.iab.create(urlRegex);
  }


  goToProfile(event, user_id) {
  	event.stopPropagation();
  
    if(this.founder) return;

  	this.navCtrl.push(ProfilePage, {
  		user_id: user_id
  	});
  }

  goToComment(event, item) {
    event.stopPropagation();
    this.navCtrl.push(ShowCardPage, {
      item: item
    });
  }

  upvote (e, item) {
    if (this.vote == 1) return;
    e.stopPropagation();
    let data = {post_id: item.id, card_id: item.card_id, user_id: this.auth.currentUser.id, score: 1};
    this.wall.vote(data).subscribe(
      success => console.log('like success', success),
      error => console.log(error)
    );
    // dangerous optimism!
    if(this.vote==-1) item.score++;
    this.vote = 1;
    item.score++;
  }

  downvote (e, item) {
    if (this.vote == -1) return;
    e.stopPropagation();
    let data = {post_id: item.id, card_id: item.card_id, user_id: this.auth.currentUser.id, score: -1};
    this.wall.vote(data).subscribe(
      success => console.log('dislike success', success),
      error => console.log(error)
    );
    // dangerous optimism!
    if(this.vote==1) item.score--;
    this.vote = -1;
    item.score--;
  }

  appendResponse(item){
    this.item.responses.push(item);
  }

}
