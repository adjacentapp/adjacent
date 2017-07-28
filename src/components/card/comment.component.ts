import { Component, Input } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import { ProfilePage } from '../../pages/profile/profile';
import { ShowCardPage } from '../../pages/card/show';
import { WallProvider, Comment } from '../../providers/wall/wall';
import { AuthProvider } from '../../providers/auth/auth';

@Component({
  selector: 'comment-component',
  templateUrl: 'comment-component.html'
})
export class CommentComponent {
  @Input() item: Comment;
  @Input() showVotes: boolean = true;
  vote: number;

  constructor(public navCtrl: NavController, public navParams: NavParams, private wall: WallProvider, private auth: AuthProvider) {}

  ngOnInit() {
    for (let liker_id of this.item.likes)
      if(liker_id == this.auth.currentUser.id)
        this.vote = 1;
    for (let disliker_id of this.item.dislikes)
      if(disliker_id == this.auth.currentUser.id)
        this.vote = -1;
  }

  goToProfile(event, user_id) {
  	event.stopPropagation();
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

}
