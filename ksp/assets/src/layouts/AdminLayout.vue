<template>
  <!-- ===== Page Wrapper Start ===== -->
  <div class="flex h-screen overflow-hidden">
    <!-- ===== Sidebar Start ===== -->
    <SidebarArea />
    <!-- ===== Sidebar End ===== -->

    <!-- ===== Content Area Start ===== -->
    <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
      <!-- ===== Header Start ===== -->
      <HeaderArea />
      <!-- ===== Header End ===== -->

      <!-- ===== Main Content Start ===== -->
      <main>
        <div class="w-5/6 mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
          <router-view/>
        </div>
      </main>
      <!-- ===== Main Content End ===== -->
    </div>
  </div>
  <!-- ===== Page Wrapper End ===== -->
</template>

<script>
import SidebarArea from "../components/admin/Sidebar/SidebarArea.vue";
import HeaderArea from "../components/admin/Header/HeaderArea.vue";


export default {
  name: "AdminLayout",
  components: {HeaderArea, SidebarArea},
  computed: {
    adminRoutes() {
      const adminBaseRoute = this.$router.getRoutes().find(route => route.name === 'admin');
      return adminBaseRoute?.children || [];
    }
  }
};
</script>




