import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { CardComponent } from './card';

@NgModule({
  declarations: [
    CardComponent,
  ],
  imports: [
    IonicPageModule.forChild(CardComponent),
  ],
  exports: [
    CardComponent
  ]
})
export class CardComponentModule {}
