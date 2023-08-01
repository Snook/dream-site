<?php $this->setScript('head', SCRIPT_PATH . '/admin/access_agreement.js'); ?>
<?php $this->setOnload('access_agreement_init();'); ?>
<?php $this->assign('page_title','Access Agreement'); ?>
<?php $this->assign('topnav','guests'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<?php if (!$this->print_view) { ?>
<a href="main.php?<?php echo $_SERVER['QUERY_STRING']; ?>&amp;print_view=true" target="_blank" class="button" style="float:right;margin-bottom:4px;">Print</a>
<?php } ?>

<h3>Dream Dinners Non-Disclosure and Non-Interference Agreement</h3>

<div class="clear"></div>

<div id="terms" style="background-color:#fff;padding:10px;margin:10px;<?php if (!$this->print_view) { ?>height:400px;overflow:auto;<?php } ?>">
<p>In consideration of my employment by or consultancy with Dream Dinners Inc. or the Dream Dinners franchisee and any compensation paid to me in connection with such employment or consultancy, I, <span style="font-weight:bold;"><?php echo CUser::getCurrentUser()->firstname; ?> <?php echo CUser::getCurrentUser()->lastname; ?></span> hereby agree as follows:</p>

<p>1. Definitions. The following terms will have the following meanings:</p>

<div style="padding:0px 20px;">

<p>(a) Dream Dinners Inc. as the franchisor and the Dream Dinners franchisee are referred to collectively as "Company" or "The Company".</p>

<p>(b) "Invention(s)" means discoveries, developments, concepts, designs, ideas, improvements, inventions and/or works of authorship (including, but not limited to, interim work product, modifications and derivative works), whether or not patentable, copyrightable or otherwise legally protectable. This includes, but is not limited to, any new product, method, procedure, process, recipe, formulation, technique, use, composition or design of any kind, or any improvement thereon.</p>

<p>(c) "Proprietary Information" means information or material not generally known or available outside the Company, held by the Company as a trade secret or proprietary in nature, or entrusted to the Company by third parties. This includes, but is not limited to, Inventions, confidential knowledge, trade secrets, copyrights, product ideas, techniques, processes, recipes, formulas and/or any other information of any type relating to products, data, research, development, marketing, forecasts, sales, pricing, customers, employees, and/or cost or other financial data concerning any of the foregoing of the Company and its operations. Proprietary Information may be contained in material such as drawings, samples, procedures, specifications, reports, studies, analyses, customer or supplier lists, budgets, cost or price lists, compilations or computer programs, or may be in the nature of unwritten knowledge or know-how.</p>

<p>(d) "Company Documents" means documents or other media that contain Proprietary Information or any other information concerning the business, operations or plans of the Company, whether such documents have been prepared by me or others. Company Documents include, but are not limited to, drawings, photographs, charts, graphs, notebooks, customer lists, computer disks, tapes or printouts, audio or video recordings and other printed, typewritten or handwritten documents.</p>

</div>

<p>2. I understand that Franchisee possesses or has rights to Proprietary Information (including certain information developed by me during my employment by or consultancy with Franchisee) which has commercial value in the Company's business. I also understand that Franchisee possesses or has license to use the Proprietary Information of the Company in connection with its franchised Dream Dinners store.</p>

<p>3. I understand that through my employment by or consultancy with Franchisee I will have access to or possession of Proprietary Information and Company Documents which are important to the Company's business.</p>

<p>4. I understand and agree that my employment by or consultancy with Franchisee creates a relationship of confidence and trust between me and Franchisee with respect to (a) all Proprietary Information; (b) all Company Documents; and (c) the confidential information of any other person or entity with which the Company has a business relationship and is required by terms of an agreement with such entity or person to hold such information as confidential.</p>

<p>5. I agree at all times, both during my employment by or consultancy with Franchisee and after its termination (regardless of the reason for such termination), to keep in confidence and trust all such Proprietary Information and Company Documents, and I will not use or disclose any such information, except in the performance of my duties and responsibilities as an employee or a consultant of Franchisee, without the prior written consent of the Company.</p>

<p>6. In addition, I hereby agree as follows:</p>

<div style="padding:0px 20px;">

<p>(a) All Proprietary Information shall be the sole property of the Company, and the Company shall be the sole owner of all trade secrets, patents, copyrights and other rights in connection therewith. I hereby assign to the Company any rights I may presently have or I may acquire in such Proprietary Information.</p>

<p>(b) All Company Documents, whether or not pertaining to Proprietary Information, furnished to me by Franchisee or produced by me or others in connection with my employment by or consultancy with Franchisee shall be and remain the sole property of the Company. I shall return to Franchisee all such Company Documents as and when requested by Franchisee. Even if Franchisee does not so request, I shall promptly return to Franchisee all Company Documents upon termination of my employment by or consultancy with Franchisee for any reason, and I will not take with me any such Company Documents, material or property or any reproduction thereof upon such termination.</p>

<p>(c) I will promptly disclose to the Company, all Inventions made or conceived, reduced to practice or learned by me, either alone or jointly with others, prior to the term of my employment by or consultancy with the Company and for one (1) year after my employment by or consultancy with the Company ceases.</p>

<p>(d) During the term of my employment by or consultancy with Franchisee, all Inventions that I conceive, reduce to practice, develop or have developed (in whole or in part, either alone or jointly with others) shall be the sole property of the Company to the maximum extent permitted by law, and the Company shall be the sole owner of all patents, copyrights, trademarks, trade secrets and other rights in connection therewith. I hereby assign to the Company any rights that I may have or acquire in such Inventions. I agree that any Invention required to be disclosed under paragraph (c) above within one (1) year after the term of my employment by or consultancy with Franchisee ceases shall be presumed to have been conceived during my employment by or consultancy with Franchisee. I understand that I may overcome the presumption by showing that such Invention was conceived after the termination of my employment by or consultancy with Franchisee, and without the use of any Proprietary Information or Company Documents.</p>

<p>(e) Any assignment of Inventions required by this Agreement does not apply to an Invention for which no equipment, supplies, facility, trade secret or proprietary information of the Company was used and which was developed entirely on the employee's own time, unless (a) the Invention relates (i) directly to the business of the Company or (ii) to the Company's actual or demonstrably anticipated research or development or (b) the Invention results from any work performed by the employee for Franchisee.</p>

<p>(f) During or after my employment by or consultancy with Franchisee, upon the Company's request and at the Company's expense, I will execute all papers in a timely manner and do all acts necessary to apply for, secure, maintain or enforce patents, copyrights, trademarks and any other legal rights in the United States and foreign countries in Inventions owned by and/or assigned to the Company under this Agreement, and I will execute all papers and do any and all acts necessary to assign and transfer to the Company or any person or party to whom the Company is obligated to assign its rights, my entire right, title and interest in and to such Inventions. This obligation shall survive the termination of my employment by or consultancy with Franchisee.</p>

</div>

<p>7. I represent that my execution of this Agreement, my employment by or consultancy with Franchisee and my performance of my proposed duties to Franchisee in the development of its business will not violate any obligations that I may have to any former employer, or other person or entity, including any obligations to keep confidential any proprietary or confidential information of any such employer, person or entity. I have not entered into, and I will not enter into, any agreement which conflicts with or would, if performed by me, cause me to breach this Agreement.</p>

<p>8. I agree that this Agreement does not constitute an employment agreement and that, unless otherwise provided in a written contract signed by Franchisee and me, (a) my employment by or consultancy with Franchisee is "at will" and (b) I shall have the right to resign my employment by or consultancy with Franchisee, and Franchisee shall have the right to terminate my employment by or consultancy with Franchisee at any time and for any reason, with or without cause.</p>

<p>9. This Agreement shall be effective as of the first day of my employment by or consultancy with Franchisee and the obligations hereunder will continue beyond the termination of such employment or consultancy and will be binding on my heirs, assigns and legal representatives. This Agreement is for the benefit of the Company, and its successors and assigns (including all subsidiaries, affiliates, joint ventures and associated companies) and is not conditioned on my employment by or consultancy with Franchisee for any period of time or compensation therefor. I agree that the Company is entitled to communicate any obligations under this Agreement to any future company by which I am employed or consulted.</p>

<p>10. During the term of my employment by or consultancy with Franchisee and for one (1) year after my employment by or consultancy ceases for any reason other than because of the Company's financial hardship, I will not, without the Company's prior written consent, directly or indirectly be employed by or involved with any business developing or exploiting any products or services that are directly and materially competitive with products or services (a) being commercially developed or exploited by the Company during my employment or consultancy and (b) on which I worked or about which I learned Proprietary Information during my employment by or consultancy with Franchisee.</p>

<p>11. During the term of my employment by or consultancy with Franchisee and for one (1) year after my employment or consultancy ceases, I will not personally or through others (a) recruit, solicit or induce in any way any employee, advisor or consultant of the Franchisee or the Company to terminate his or her relationship with the Franchisee or the Company, or (b) solicit any client or customer of the Franchisee or the Company to become clients or customers of another entity or association directly competitive to the business in which the Company is now involved or becomes involved.</p>

<p>12. I acknowledge that any violation of this Agreement by me will cause irreparable injury to the Company and I agree that the Company will be entitled to extraordinary relief in court, including, but not limited to, temporary restraining orders, preliminary injunctions and permanent injunctions without the necessity of posting a bond or other security and without prejudice to any other rights and remedies that the Company may have for a breach of this Agreement.</p>

<p>13. I agree that any dispute in the meaning, effect or validity of this Agreement shall be resolved in accordance with the laws of the State of Washington without regard to the conflict of law provisions thereof. Venue and jurisdiction of any claim or action involving this Agreement or my employment by or consultancy with the Company shall exist exclusively in the state and federal courts in King County, Washington, unless injunctive relief is sought by the Company and, in the Company's judgment, may not be effective unless obtained in some other venue. I further agree that if one or more provisions of this Agreement are held to be unenforceable under applicable Washington law, such provision(s) shall be excluded from this Agreement and the balance of the Agreement shall be interpreted as if such provision were so excluded and shall be enforceable in accordance with its terms.</p>
</div>

<?php if (!$this->print_view) { ?>
<div style="padding:0px 20px;">
	<form method="post" onsubmit="return _check_form(this);">
	<input type="hidden" name="back" value="<?php echo $this->back; ?>" />
	<p>I, <span style="font-weight:bold;"><?php echo strtoupper(CUser::getCurrentUser()->firstname); ?> <?php echo strtoupper(CUser::getCurrentUser()->lastname); ?></span>, HAVE READ AND FULLY UNDERSTAND THIS AGREEMENT. THIS AGREEMENT MAY ONLY BE MODIFIED BY A SUBSEQUENT WRITTEN AGREEMENT EXECUTED BY ME AND THE COMPANY.</p>
	<?php if ($this->read_only) { ?>
	<input type="checkbox" data-tooltip="Check to agree to the terms of the agreement" disabled="disabled" />
	<input type="submit" value="I Agree" disabled="disabled" class="button" /> <span style="color: red;">*Viewing page in read only mode.</span>
	<?php } else { ?>
	<input type="checkbox" id="agree_to_nda" name="agree_to_nda" data-tooltip="Check to agree to the terms of the agreement" data-dd_required="true" />
	<input type="submit" id="agree_to_nda_submit" name="agree_to_nda_submit" value="I Agree" disabled="disabled" class="button" /> <span id="read_all_notice">*Please read entire agreement, thank you.</span>
	<?php } ?>
	</form>
</div>
<?php } ?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>