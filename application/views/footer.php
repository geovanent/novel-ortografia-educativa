</div></main>
<footer class="novel-footer"><p>Novel · Academia de Novéis · IFBA</p></footer>
<?php $notification=$this->session->flashdata('notification'); if($notification) echo novel_notify($notification['message'],$notification['type']); ?>
</body></html>
