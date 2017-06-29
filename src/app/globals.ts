'use strict';

let DEV = true;
DEV = false;

let localURL 	= "http://localhost/~salsaia/adjacent/api/v2/";
let remoteURL 	= "http://adjacent.wuex59etyj.us-west-2.elasticbeanstalk.com/api/v2/";

export const BASE_API_URL = DEV ? localURL : remoteURL;

// export const INDUSTRIES = ['Agriculture', 'Art', 'Architecture', 'Business', 'Computer Science', 'Design', 'Education', 'Engineering', /*'Entrepreneurship',*/ 'Finance', 'Government', 'Healthcare', 'Humanities', 'Journalism', 'Languages', 'Law', 'Marketing', 'Math', 'Music', 'Performing Arts', 'Science', 'Social Impact', 'Sports', 'Writing'];
export const INDUSTRIES: string[] = ['Agriculture', 'Art', 'Architecture', 'Business', 'Computer Science', 'Design', 'Education', 'Engineering', 'Entrepreneurship', 'Finance', 'Government', 'Healthcare', 'Humanities', 'Journalism', 'Languages', 'Law', 'Lifestyle', 'Marketing', 'Math', 'Music', 'Performing Arts', 'Policy Planning', 'Science', 'Social Impact', 'Sports', 'Writing'];
export const STAGES: string[] = ['Couch Entrepreneur', 'Taking First Step', '"Kickstarting" The Engine', 'Running The Company', 'Scaling The Business', '$$$'];
export const SKILLS: string[] = ['Business', 'Customer Discovery', 'Design', 'Development', 'Marketing', 'User Experience', 'Customer discovery'];
export const NETWORKS: string[] = ['Public', 'Stony Brook University', 'ThinkLab Incubator', 'HCI Meetup'];

export let firstFollow = true;
export let setFirstFollowFalse = () => {
	firstFollow = false;
};